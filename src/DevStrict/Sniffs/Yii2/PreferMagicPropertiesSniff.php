<?php

declare(strict_types=1);

namespace DevStrict\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Suggests using magic properties instead of getter methods in Yii2.
 *
 * Yii2 components support magic properties through __get() method,
 * allowing you to access getters as properties for cleaner code.
 *
 * Examples:
 * - Yii::$app->user->getId() -> Yii::$app->user->id
 * - Yii::$app->user->getIdentity() -> Yii::$app->user->identity
 * - $model->getName() -> $model->name
 */
class PreferMagicPropertiesSniff implements Sniff
{
    /**
     * List of getter methods that can be replaced with properties.
     * Key is the method name (without 'get' prefix), value is the property name.
     *
     * @var array<string, string>
     */
    private array $getterToPropertyMap = [
        'Id' => 'id',
        'Identity' => 'identity',
        'Name' => 'name',
        'Title' => 'title',
        'Description' => 'description',
        'Status' => 'status',
        'Type' => 'type',
        'Value' => 'value',
        'Data' => 'data',
        'Content' => 'content',
        'Url' => 'url',
        'Email' => 'email',
        'Username' => 'username',
        'Password' => 'password',
        'IsGuest' => 'isGuest',
        'CanAdmin' => 'canAdmin',
    ];

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

        $methodName = $token['content'];

        if (!$this->isGetterMethod($methodName)) {
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

        $propertyName = $this->getPropertyName($methodName);

        if ($propertyName === null) {
            return;
        }

        $error = sprintf(
            'Use magic property "%s" instead of getter method "%s()" for cleaner code',
            $propertyName,
            $methodName
        );

        $phpcsFile->addWarning($error, $stackPtr, 'Found');
    }

    /**
     * Check if method name looks like a getter.
     */
    private function isGetterMethod(string $methodName): bool
    {
        if (str_starts_with($methodName, 'get') && mb_strlen($methodName) > 3) {
            return true;
        }

        if (str_starts_with($methodName, 'is') && mb_strlen($methodName) > 2) {
            return true;
        }

        return str_starts_with($methodName, 'can') && mb_strlen($methodName) > 3;
    }

    /**
     * Get the property name for a getter method.
     */
    private function getPropertyName(string $methodName): ?string
    {
        if (str_starts_with($methodName, 'get')) {
            $withoutGet = mb_substr($methodName, 3);

            return $this->getterToPropertyMap[$withoutGet] ?? lcfirst($withoutGet);
        }

        if (str_starts_with($methodName, 'is')) {
            $withoutIs = mb_substr($methodName, 2);

            return $this->getterToPropertyMap[$withoutIs] ?? 'is' . $withoutIs;
        }

        if (str_starts_with($methodName, 'can')) {
            $withoutCan = mb_substr($methodName, 3);

            return $this->getterToPropertyMap[$withoutCan] ?? 'can' . $withoutCan;
        }

        return null;
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
}
