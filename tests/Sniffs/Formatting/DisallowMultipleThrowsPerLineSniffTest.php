<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Formatting;

use DevStrict\Tests\BaseTest;

/**
 * Tests for DisallowMultipleThrowsPerLineSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowMultipleThrowsPerLineSniffTest extends BaseTest
{
    private const SNIFF = 'DevStrict.Formatting.DisallowMultipleThrowsPerLine';

    public function testDetectsMultipleExceptionsOnSingleLine(): void
    {
        $code = '<?php
        /**
         * @throws JsonException|Exception
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Each @throws annotation must contain only one exception type');
    }

    public function testAllowsSingleExceptionPerThrows(): void
    {
        $code = '<?php
        /**
         * @throws JsonException
         * @throws Exception
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDetectsThreeExceptions(): void
    {
        $code = '<?php
        /**
         * @throws InvalidArgumentException|RuntimeException|LogicException
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'InvalidArgumentException, RuntimeException, LogicException');
    }

    public function testAllowsSingleFullyQualifiedException(): void
    {
        $code = '<?php
        /**
         * @throws \App\Exception\CustomException
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDetectsMultipleFullyQualifiedExceptions(): void
    {
        $code = '<?php
        /**
         * @throws \App\Exception\FirstException|\App\Exception\SecondException
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Each @throws annotation must contain only one exception type');
    }

    public function testAllowsThrowsWithDescription(): void
    {
        $code = '<?php
        /**
         * @throws JsonException When JSON parsing fails
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testIgnoresOtherDocTags(): void
    {
        $code = '<?php
        /**
         * @param string|int $value
         * @return string|null
         */
        function test($value) {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testHandlesClassDocBlock(): void
    {
        $code = '<?php
        class TestClass
        {
            /**
             * @throws Exception|RuntimeException
             */
            public function method(): void {}
        }
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Each @throws annotation must contain only one exception type');
    }
}
