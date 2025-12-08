<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects unnecessary PHP tag switching (closing and immediately reopening PHP tags).
 *
 * Multiple consecutive `?>` followed by `<?php` or `<?=` make code harder to read
 * and are usually a sign that the code should be refactored to stay in PHP mode.
 */
class DisallowConsecutivePhpTagsSniff implements Sniff
{
    /**
     * Maximum allowed consecutive PHP tag switches before triggering a warning.
     * Set to 2 to allow occasional switches but flag repeated patterns.
     */
    public int $maxConsecutiveSwitches = 2;

    /**
     * Track which close tags we've already processed as part of a sequence.
     *
     * @var array<int, bool>
     */
    private array $processedTags = [];

    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [T_CLOSE_TAG];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // Reset processed tags for each new file
        static $currentFile = '';
        $filename = $phpcsFile->getFilename();
        if ($currentFile !== $filename) {
            $currentFile = $filename;
            $this->processedTags = [];
        }

        // Skip if we've already processed this tag as part of a sequence
        if (isset($this->processedTags[$stackPtr])) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        // Count consecutive close->open tag pairs and collect all involved close tags
        [$consecutiveCount, $involvedCloseTags] = $this->countConsecutiveTagSwitches($tokens, $stackPtr);

        if ($consecutiveCount >= $this->maxConsecutiveSwitches) {
            $warning = sprintf(
                'Found %d consecutive PHP tag switches (?>...<?php). Consider staying in PHP mode and using echo/print for output.',
                $consecutiveCount
            );

            $phpcsFile->addWarning($warning, $stackPtr, 'TooManyTagSwitches');

            // Mark all involved close tags as processed
            foreach ($involvedCloseTags as $tagPtr) {
                $this->processedTags[$tagPtr] = true;
            }
        }
    }

    /**
     * Count how many consecutive close->open tag pairs exist starting from the given position.
     *
     * @param array<int, array<string, mixed>> $tokens
     * @return array{0: int, 1: array<int>} Count and list of involved close tag pointers
     */
    private function countConsecutiveTagSwitches(array $tokens, int $startPtr): array
    {
        $count = 0;
        $involvedCloseTags = [];
        $ptr = $startPtr;
        $totalTokens = count($tokens);

        while ($ptr < $totalTokens) {
            // Current token should be a close tag
            if ($tokens[$ptr]['code'] !== T_CLOSE_TAG) {
                break;
            }

            // Find next non-whitespace, non-inline-html token
            $nextPhpTag = $this->findNextPhpOpenTag($tokens, $ptr + 1, $totalTokens);

            if ($nextPhpTag === null) {
                break;
            }

            // Check if there's only whitespace/HTML between close and open tags
            if ($this->hasOnlyWhitespaceOrMinimalHtml($tokens, $ptr + 1, $nextPhpTag)) {
                $count++;
                $involvedCloseTags[] = $ptr;

                // Find the next close tag after this open tag
                $nextCloseTag = $this->findNextCloseTag($tokens, $nextPhpTag + 1, $totalTokens);

                if ($nextCloseTag === null) {
                    break;
                }

                $ptr = $nextCloseTag;
            } else {
                break;
            }
        }

        return [$count, $involvedCloseTags];
    }

    /**
     * Find the next PHP open tag (<?php or <?=).
     *
     * @param array<int, array<string, mixed>> $tokens
     */
    private function findNextPhpOpenTag(array $tokens, int $start, int $end): ?int
    {
        for ($i = $start; $i < $end; $i++) {
            if ($tokens[$i]['code'] === T_OPEN_TAG || $tokens[$i]['code'] === T_OPEN_TAG_WITH_ECHO) {
                return $i;
            }

            // If we hit actual PHP code, stop
            if (!in_array($tokens[$i]['code'], [T_INLINE_HTML, T_WHITESPACE], true)) {
                return null;
            }
        }

        return null;
    }

    /**
     * Find the next PHP close tag.
     *
     * @param array<int, array<string, mixed>> $tokens
     */
    private function findNextCloseTag(array $tokens, int $start, int $end): ?int
    {
        for ($i = $start; $i < $end; $i++) {
            if ($tokens[$i]['code'] === T_CLOSE_TAG) {
                return $i;
            }
        }

        return null;
    }

    /**
     * Check if the content between two positions is only whitespace or minimal HTML (newlines, spaces).
     *
     * @param array<int, array<string, mixed>> $tokens
     */
    private function hasOnlyWhitespaceOrMinimalHtml(array $tokens, int $start, int $end): bool
    {
        for ($i = $start; $i < $end; $i++) {
            $code = $tokens[$i]['code'];

            if ($code === T_WHITESPACE) {
                continue;
            }

            if ($code === T_INLINE_HTML) {
                // Check if the inline HTML is only whitespace
                $content = trim($tokens[$i]['content']);
                if ($content !== '') {
                    return false;
                }
                continue;
            }

            // Any other token means there's real content
            return false;
        }

        return true;
    }
}
