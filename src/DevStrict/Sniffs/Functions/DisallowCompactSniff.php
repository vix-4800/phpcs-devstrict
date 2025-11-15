<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ValueError;

/**
 * Disallows usage of `compact()` function in favor of explicit array syntax.
 *
 * The compact() function creates an array from variables and their values,
 * but it makes code less explicit and harder to track variable usage.
 * Using explicit array syntax is more readable and maintainable.
 */
class DisallowCompactSniff implements Sniff
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
     *
     * @throws ValueError
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        if (!isset($token['content']) || mb_strtolower((string) $token['content']) !== 'compact') {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken !== false && isset($tokens[$prevToken])) {
            $prevTokenCode = $tokens[$prevToken]['code'];

            if (in_array($prevTokenCode, [T_OBJECT_OPERATOR, T_DOUBLE_COLON], true)) {
                return;
            }

            if ($prevTokenCode === T_FUNCTION) {
                return;
            }
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($nextToken === false || !isset($tokens[$nextToken]) || $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $error = 'Usage of compact() is forbidden; use explicit array syntax instead (e.g., [\'var\' => $var])';
        $phpcsFile->addError($error, $stackPtr, 'Found');
    }
}
