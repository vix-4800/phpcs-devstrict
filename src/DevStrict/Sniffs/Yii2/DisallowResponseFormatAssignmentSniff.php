<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows direct assignment to Yii::$app->response->format.
 *
 * In Yii2 controllers, it's better to use methods like $this->asJson()
 * which automatically set the response format.
 */
class DisallowResponseFormatAssignmentSniff implements Sniff
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

        if ($token['content'] !== 'Yii') {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken !== false) {
            $prevTokenCode = $tokens[$prevToken]['code'];

            if (in_array($prevTokenCode, [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true)) {
                return;
            }
        }

        $current = $stackPtr;

        $next = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_DOUBLE_COLON) {
            return;
        }

        $current = $next;

        $next = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_VARIABLE || $tokens[$next]['content'] !== '$app') {
            return;
        }

        $current = $next;

        $next = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $current = $next;

        $next = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_STRING || $tokens[$next]['content'] !== 'response') {
            return;
        }

        $current = $next;

        $next = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $current = $next;

        $next = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_STRING || $tokens[$next]['content'] !== 'format') {
            return;
        }

        $current = $next;

        $next = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_EQUAL) {
            return;
        }

        $error = 'Direct assignment to Yii::$app->response->format is discouraged; use controller methods like $this->asJson() instead';
        $phpcsFile->addWarning($error, $stackPtr, 'Found');
    }
}
