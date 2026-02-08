<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Enforces that multi-line method chains keep one call per line and that inline calls aren't mixed in.
 */
class MethodChainingPerLineSniff implements Sniff
{
    use MethodChainHelperTrait;

    /**
     * Keeps track of inline operators already reported.
     *
     * @var array<int, bool>
     */
    private array $reportedInlineOperators = [];

    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if (!$this->isMultiLineOperator($phpcsFile, $stackPtr)) {
            return;
        }

        $this->ensureOnlyOneCallPerLine($phpcsFile, $stackPtr);
        $this->flagInlineCallsBefore($phpcsFile, $stackPtr);
    }

    /**
     * Ensures there is only one chained call per physical line once the chain spans multiple lines.
     */
    private function ensureOnlyOneCallPerLine(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];

        for ($ptr = $stackPtr + 1, $count = count($tokens); $ptr < $count && $tokens[$ptr]['line'] === $line; ++$ptr) {
            if (!in_array($tokens[$ptr]['code'], [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                continue;
            }

            if (!$this->isSameChainContext($phpcsFile, $tokens, $stackPtr, $ptr)) {
                continue;
            }

            if ($this->hasChainBreakBetween($phpcsFile, $stackPtr, $ptr)) {
                continue;
            }

            $phpcsFile->addError(
                'Only one chained method call is allowed per line when the chain spans multiple lines',
                $ptr,
                'MultipleCallsPerLine',
            );

            break;
        }
    }

    /**
     * Flags inline calls that appear before the first multi-line segment in the same chain.
     */
    private function flagInlineCallsBefore(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $searchPtr = $stackPtr - 1;

        while (($prev = $phpcsFile->findPrevious([T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], $searchPtr, null, false)) !== false) {
            if ($this->hasChainBreakBetween($phpcsFile, $prev, $stackPtr)) {
                break;
            }

            if (!$this->isSameChainContext($phpcsFile, $tokens, $stackPtr, $prev)) {
                break;
            }

            if ($this->isMultiLineOperator($phpcsFile, $prev)) {
                break;
            }

            if ($this->isFirstOperatorInChain($phpcsFile, $prev)) {
                $searchPtr = $prev - 1;

                continue;
            }

            if (!isset($this->reportedInlineOperators[$prev])) {
                $this->reportedInlineOperators[$prev] = true;
                $phpcsFile->addError(
                    'Move this chained call to its own line to keep the multi-line chain consistent',
                    $prev,
                    'InlineBeforeMultiline',
                );
            }

            $searchPtr = $prev - 1;
        }
    }

    /**
     * Determines whether the provided operator is the first chained call in its context.
     */
    private function isFirstOperatorInChain(File $phpcsFile, int $operatorPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $searchPtr = $operatorPtr - 1;

        while (($prev = $phpcsFile->findPrevious([T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], $searchPtr, null, false)) !== false) {
            if ($this->hasChainBreakBetween($phpcsFile, $prev, $operatorPtr)) {
                return true;
            }

            return !$this->isSameChainContext($phpcsFile, $tokens, $operatorPtr, $prev);
        }

        return true;
    }

    /**
     * Checks whether two operators share the same chain context (scope and parenthesis nesting).
     */
    private function isSameChainContext(File $phpcsFile, array $tokens, int $firstPtr, int $secondPtr): bool
    {
        return $tokens[$firstPtr]['level'] === $tokens[$secondPtr]['level']
            && $this->buildContextKey($phpcsFile, $tokens, $firstPtr) === $this->buildContextKey($phpcsFile, $tokens, $secondPtr);
    }

    /**
     * Creates a comparable context key from nested parenthesis and conditions info.
     */
    private function buildContextKey(File $phpcsFile, array $tokens, int $stackPtr): string
    {
        $token = $tokens[$stackPtr];
        $conditions = $token['conditions'] ?? [];
        $nested = $token['nested_parenthesis'] ?? [];
        $bracketContext = $this->buildBracketContextKey($phpcsFile, $tokens, $stackPtr);

        $conditionPart = $conditions === [] ? '' : implode(',', array_keys($conditions));
        $nestedPart = $nested === [] ? '' : implode(',', array_keys($nested));

        return $conditionPart . '|' . $nestedPart . '|' . $bracketContext;
    }

    /**
     * Builds a context key representing nested array and bracket access levels.
     */
    private function buildBracketContextKey(File $phpcsFile, array $tokens, int $stackPtr): string
    {
        $openers = array_merge(
            $this->collectBracketOpeners($phpcsFile, $tokens, $stackPtr, T_OPEN_SHORT_ARRAY, 'bracket_closer'),
            $this->collectBracketOpeners($phpcsFile, $tokens, $stackPtr, T_OPEN_SQUARE_BRACKET, 'bracket_closer'),
            $this->collectBracketOpeners($phpcsFile, $tokens, $stackPtr, T_ARRAY, 'parenthesis_closer'),
        );

        if ($openers === []) {
            return '';
        }

        sort($openers);
        $openers = array_unique($openers);

        return implode(',', $openers);
    }

    /**
     * Collects bracket or array openers that enclose the provided pointer.
     *
     * @return array<int>
     */
    private function collectBracketOpeners(
        File $phpcsFile,
        array $tokens,
        int $stackPtr,
        int|string $openerCode,
        string $closerKey,
    ): array {
        $openers = [];
        $searchPtr = $stackPtr;

        while (($opener = $phpcsFile->findPrevious($openerCode, $searchPtr - 1, null, false)) !== false) {
            $closer = $tokens[$opener][$closerKey] ?? null;

            if ($closer !== null && $closer > $stackPtr) {
                $openers[] = $opener;
            }

            $searchPtr = $opener - 1;
        }

        return $openers;
    }
}
