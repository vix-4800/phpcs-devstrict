<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Suggests using exists() instead of count() > 0 for ActiveQuery existence checks.
 *
 * The exists() method is more efficient than count() when you only need to check
 * if any records exist, as it stops after finding the first match instead of counting all records.
 *
 * Examples:
 * - ->count() > 0 should be ->exists()
 * - ->count() >= 1 should be ->exists()
 * - ->count() !== 0 should be ->exists()
 * - ->count() == 0 should be !->exists()
 * - ->count() < 1 should be !->exists()
 */
class PreferExistsOverCountSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * Processes this test when one of its tokens is encountered.
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        if ($token['content'] !== 'count') {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken === false || $tokens[$prevToken]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($nextToken === false || $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$nextToken]['parenthesis_closer'])) {
            return;
        }

        $closeParen = $tokens[$nextToken]['parenthesis_closer'];
        $hasArguments = $this->hasArgumentsBetween($phpcsFile, $nextToken, $closeParen);

        if ($hasArguments) {
            return;
        }

        $comparisonStart = $phpcsFile->findNext(T_WHITESPACE, $closeParen + 1, null, true);

        if ($comparisonStart === false) {
            return;
        }

        $comparisonToken = $tokens[$comparisonStart];
        $comparison = $this->getComparison($phpcsFile, $comparisonStart);

        if ($comparison === null) {
            return;
        }

        $this->reportViolation($phpcsFile, $stackPtr, $comparison);
    }

    /**
     * Check if there are any arguments between parentheses.
     */
    private function hasArgumentsBetween(File $phpcsFile, int $openParen, int $closeParen): bool
    {
        $tokens = $phpcsFile->getTokens();

        for ($i = $openParen + 1; $i < $closeParen; $i++) {
            if (in_array($tokens[$i]['code'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Get the comparison type if it's a count existence check.
     *
     * @return array{operator: string, value: string, shouldNegate: bool}|null
     */
    private function getComparison(File $phpcsFile, int $startPtr): ?array
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$startPtr];

        $operatorMap = [
            T_GREATER_THAN => '>',
            T_IS_GREATER_OR_EQUAL => '>=',
            T_IS_NOT_EQUAL => '!=',
            T_IS_NOT_IDENTICAL => '!==',
            T_IS_EQUAL => '==',
            T_IS_IDENTICAL => '===',
            T_LESS_THAN => '<',
            T_IS_SMALLER_OR_EQUAL => '<=',
        ];

        if (!isset($operatorMap[$token['code']])) {
            return null;
        }

        $operator = $operatorMap[$token['code']];

        $valuePtr = $phpcsFile->findNext(T_WHITESPACE, $startPtr + 1, null, true);

        if ($valuePtr === false) {
            return null;
        }

        $valueToken = $tokens[$valuePtr];

        if ($valueToken['code'] !== T_LNUMBER) {
            return null;
        }

        $value = $valueToken['content'];

        $shouldNegate = false;

        if (
            ($operator === '>' && $value === '0') ||
            ($operator === '>=' && $value === '1') ||
            ($operator === '!=' && $value === '0') ||
            ($operator === '!==' && $value === '0')
        ) {
            $shouldNegate = false;
        } elseif (
            ($operator === '==' && $value === '0') ||
            ($operator === '===' && $value === '0') ||
            ($operator === '<' && $value === '1') ||
            ($operator === '<=' && $value === '0')
        ) {
            $shouldNegate = true;
        } else {
            return null;
        }

        return [
            'operator' => $operator,
            'value' => $value,
            'shouldNegate' => $shouldNegate,
        ];
    }

    /**
     * Report a violation.
     *
     * @param array{operator: string, value: string, shouldNegate: bool} $comparison
     */
    private function reportViolation(File $phpcsFile, int $stackPtr, array $comparison): void
    {
        $suggestion = $comparison['shouldNegate'] ? '!...->exists()' : '...->exists()';
        $pattern = sprintf('count() %s %s', $comparison['operator'], $comparison['value']);

        $message = sprintf(
            'Use %s instead of %s for better performance when checking record existence',
            $suggestion,
            $pattern
        );

        $phpcsFile->addWarning($message, $stackPtr, 'Found');
    }
}
