<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows file_get_contents() for HTTP and HTTPS requests.
 *
 * Bad:
 * file_get_contents('https://example.com');
 * file_get_contents($url, false, stream_context_create(['http' => ['method' => 'POST']]));
 *
 * Good:
 * file_get_contents('/tmp/local-file.txt');
 * $client->get('https://example.com');
 */
final class DisallowHttpFileGetContentsSniff implements Sniff
{
    /**
     * Returns an array of tokens this sniff wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_STRING, T_NAME_FULLY_QUALIFIED];
    }

    /**
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        $functionName = mb_strtolower((string) $tokens[$stackPtr]['content']);

        if ($functionName !== 'file_get_contents' && $functionName !== '\file_get_contents') {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if (is_int($prevToken)) {
            $prevTokenCode = (int) $tokens[$prevToken]['code'];

            if (in_array($prevTokenCode, [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                return;
            }

            if ($prevTokenCode === T_FUNCTION) {
                return;
            }
        }

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if (!is_int($openParen) || (int) $tokens[$openParen]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $closeParen = $tokens[$openParen]['parenthesis_closer'] ?? null;

        if (!is_int($closeParen)) {
            return;
        }

        if (!$this->isHttpRequest($phpcsFile, $openParen, $closeParen)) {
            return;
        }

        $phpcsFile->addError(
            'Use of file_get_contents() for HTTP requests is forbidden; use a dedicated HTTP client instead',
            $stackPtr,
            'Found',
        );
    }

    /**
     * Detects whether the file_get_contents() call is used for an HTTP request.
     *
     * @param File $phpcsFile
     * @param int  $openParen
     * @param int  $closeParen
     */
    private function isHttpRequest(File $phpcsFile, int $openParen, int $closeParen): bool
    {
        return $this->firstArgumentStartsWithHttpScheme($phpcsFile, $openParen, $closeParen)
            || $this->hasHttpStreamContext($phpcsFile, $openParen, $closeParen);
    }

    /**
     * Checks whether the first argument is a literal HTTP or HTTPS URL.
     *
     * @param File $phpcsFile
     * @param int  $openParen
     * @param int  $closeParen
     */
    private function firstArgumentStartsWithHttpScheme(File $phpcsFile, int $openParen, int $closeParen): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        $firstToken = $phpcsFile->findNext(T_WHITESPACE, $openParen + 1, $closeParen, true);

        while (is_int($firstToken) && (int) $tokens[$firstToken]['code'] === T_OPEN_PARENTHESIS) {
            $firstToken = $phpcsFile->findNext(T_WHITESPACE, $firstToken + 1, $closeParen, true);
        }

        if (!is_int($firstToken)) {
            return false;
        }

        if (!in_array((int) $tokens[$firstToken]['code'], [T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING], true)) {
            return false;
        }

        return preg_match('/^[\'"]https?:\/\//i', (string) $tokens[$firstToken]['content']) === 1;
    }

    /**
     * Checks for stream_context_create() with the HTTP wrapper configuration.
     *
     * @param File $phpcsFile
     * @param int  $openParen
     * @param int  $closeParen
     */
    private function hasHttpStreamContext(File $phpcsFile, int $openParen, int $closeParen): bool
    {
        $content = mb_strtolower($phpcsFile->getTokensAsString($openParen + 1, $closeParen - $openParen - 1));

        return preg_match('/stream_context_create\s*\(.*[\'"]https?[\'"]\s*=>/s', $content) === 1;
    }
}
