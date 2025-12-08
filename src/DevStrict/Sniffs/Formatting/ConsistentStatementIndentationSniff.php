<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures all statements inside the same scope have consistent indentation.
 *
 * This sniff checks that consecutive statements at the same nesting level
 * have the same indentation.
 */
class ConsistentStatementIndentationSniff implements Sniff
{
    /**
     * The number of spaces for one indentation level.
     */
    public int $indent = 4;

    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        // Only register for statement-starting tokens
        return [
            T_ECHO,
            T_PRINT,
            T_RETURN,
            T_IF,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_SWITCH,
            T_TRY,
            T_THROW,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        // Only process if this token is the first non-whitespace on its line
        $firstOnLine = $phpcsFile->findFirstOnLine(T_WHITESPACE, $stackPtr, true);
        if ($firstOnLine !== $stackPtr) {
            return;
        }

        // Get the nesting level and conditions of this token
        $currentLevel = $token['level'] ?? 0;
        $currentConditions = $token['conditions'] ?? [];
        $currentIndent = $token['column'] - 1;

        // Find the next statement at the same level with same conditions
        $nextStatement = $this->findNextStatementAtSameLevel($phpcsFile, $stackPtr, $currentLevel, $currentConditions);
        if ($nextStatement === null) {
            return;
        }

        $nextIndent = $tokens[$nextStatement]['column'] - 1;

        // If indentation differs and current has more spaces, report warning
        if ($currentIndent !== $nextIndent && $currentIndent > $nextIndent) {
            $error = sprintf(
                'Statement indentation is inconsistent with next statement at same level; found %d spaces but next statement has %d',
                $currentIndent,
                $nextIndent
            );

            $fix = $phpcsFile->addFixableWarning($error, $stackPtr, 'InconsistentIndentation');
            if ($fix === true) {
                $this->fixIndentation($phpcsFile, $stackPtr, (int) $nextIndent);
            }
        }
    }

    /**
     * Find the next statement at the same nesting level with same conditions.
     *
     * @param array<int, int> $conditions
     */
    private function findNextStatementAtSameLevel(File $phpcsFile, int $stackPtr, int $level, array $conditions): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $currentLine = $tokens[$stackPtr]['line'];

        $statementTokens = [
            T_ECHO,
            T_PRINT,
            T_RETURN,
            T_IF,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_SWITCH,
            T_TRY,
            T_THROW,
            T_STRING, // For static method calls like Modal::end()
        ];

        // Search forward for the next statement
        for ($i = $stackPtr + 1; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            // Skip if on the same line
            if ($token['line'] === $currentLine) {
                continue;
            }

            // Check if this is a statement token
            if (!in_array($token['code'], $statementTokens, true)) {
                continue;
            }

            // Check if it's the first token on its line
            $firstOnLine = $phpcsFile->findFirstOnLine(T_WHITESPACE, $i, true);
            if ($firstOnLine !== $i) {
                continue;
            }

            // Check level and conditions
            $tokenLevel = $token['level'] ?? 0;
            $tokenConditions = $token['conditions'] ?? [];

            if ($tokenLevel === $level && $tokenConditions === $conditions) {
                return $i;
            }

            // If we hit a lower level, stop searching
            if ($tokenLevel < $level) {
                return null;
            }
        }

        return null;
    }

    /**
     * Fix the indentation.
     */
    private function fixIndentation(File $phpcsFile, int $stackPtr, int $expectedIndent): void
    {
        $tokens = $phpcsFile->getTokens();
        $actualIndent = $tokens[$stackPtr]['column'] - 1;

        if ($actualIndent === 0) {
            $phpcsFile->fixer->addContentBefore($stackPtr, str_repeat(' ', $expectedIndent));
        } else {
            // Find the whitespace token before
            $whitespace = $stackPtr - 1;
            if ($tokens[$whitespace]['code'] === T_WHITESPACE) {
                // Need to preserve newline and replace spaces
                $content = $tokens[$whitespace]['content'];
                $newContent = preg_replace('/\n[ ]*$/', "\n" . str_repeat(' ', $expectedIndent), $content);
                if (is_string($newContent)) {
                    $phpcsFile->fixer->replaceToken($whitespace, $newContent);
                }
            }
        }
    }
}
