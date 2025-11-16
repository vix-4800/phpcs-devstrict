<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows usage of `throw` expressions inside ternary operators.
 */
class DisallowThrowInTernarySniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     */
    public function register(): array
    {
        return [T_THROW];
    }

    /**
     * Processes this test when one of its tokens is encountered.
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $ternaryToken = $phpcsFile->findPrevious(
            [T_INLINE_THEN, T_INLINE_ELSE, T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET],
            $stackPtr - 1,
            null,
            false,
        );

        if ($ternaryToken === false) {
            return;
        }

        $tokenCode = $tokens[$ternaryToken]['code'];

        if (in_array($tokenCode, [T_INLINE_THEN, T_INLINE_ELSE], true)) {
            $error = 'Throwing exceptions inside ternary operators is not allowed. Use if-else statement or extract to a separate expression for better readability.';

            $phpcsFile->addError($error, $stackPtr, 'Found');
        }
    }
}
