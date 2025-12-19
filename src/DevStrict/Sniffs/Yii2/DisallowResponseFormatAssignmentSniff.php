<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows direct assignment to Yii::$app->response->format for JSON and XML formats.
 *
 * In Yii2 controllers, use $this->asJson() or $this->asXml() instead of
 * manually setting response format.
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

        $valueToken = $phpcsFile->findNext(T_WHITESPACE, $next + 1, null, true);
        if ($valueToken === false) {
            return;
        }

        $isJsonOrXml = false;
        $formatType = null;

        if (in_array($tokens[$valueToken]['code'], [T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING], true)) {
            $value = trim($tokens[$valueToken]['content'], '"\'');
            if (in_array(strtolower($value), ['json', 'xml'], true)) {
                $isJsonOrXml = true;
                $formatType = strtolower($value);
            }
        }

        if ($tokens[$valueToken]['code'] === T_STRING) {
            $potentialClass = $valueToken;
            $doubleColon = $phpcsFile->findNext(T_WHITESPACE, $potentialClass + 1, null, true);

            if ($doubleColon !== false && $tokens[$doubleColon]['code'] === T_DOUBLE_COLON) {
                $constantToken = $phpcsFile->findNext(T_WHITESPACE, $doubleColon + 1, null, true);
                if ($constantToken !== false && $tokens[$constantToken]['code'] === T_STRING) {
                    $constantName = $tokens[$constantToken]['content'];
                    if ($constantName === 'FORMAT_JSON') {
                        $isJsonOrXml = true;
                        $formatType = 'json';
                    } elseif ($constantName === 'FORMAT_XML') {
                        $isJsonOrXml = true;
                        $formatType = 'xml';
                    }
                }
            } else {
            $constantName = $tokens[$valueToken]['content'];
            if ($constantName === 'FORMAT_JSON') {
                $isJsonOrXml = true;
                $formatType = 'json';
            } elseif ($constantName === 'FORMAT_XML') {
                $isJsonOrXml = true;
                $formatType = 'xml';
                }
            }
        }

        if (!$isJsonOrXml) {
            return;
        }

        $methodName = $formatType === 'json' ? 'asJson()' : 'asXml()';
        $error = sprintf(
            'Direct assignment to Yii::$app->response->format is discouraged; use $this->%s instead',
            $methodName
        );
        $phpcsFile->addWarning($error, $stackPtr, 'Found');
    }
}
