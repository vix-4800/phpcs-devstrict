<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\ControlStructures;

use DevStrict\Tests\BaseTest;

/**
 * Tests for DisallowThrowInTernarySniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowThrowInTernarySniffTest extends BaseTest
{
    /**
     * Test that throw in ternary operator triggers an error.
     */
    public function testThrowInTernaryTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $condition ? throw new Exception("error") : "default";', 'DevStrict.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }

    /**
     * Test that throw in second part of ternary triggers an error.
     */
    public function testThrowInSecondPartOfTernaryTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $condition ? "value" : throw new Exception("error");', 'DevStrict.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }

    /**
     * Test that throw in regular statement does not trigger error.
     */
    public function testThrowInRegularStatementDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
if ($condition) {
    throw new Exception("error");
}', 'DevStrict.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test that standalone throw does not trigger error.
     */
    public function testStandaloneThrowDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
throw new Exception("error");', 'DevStrict.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test that throw in function body does not trigger error.
     */
    public function testThrowInFunctionBodyDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
function test() {
    throw new Exception("error");
}', 'DevStrict.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test nested ternary with throw.
     */
    public function testNestedTernaryWithThrow(): void
    {
        $result = $this->runPhpcs('<?php
$result = $cond1 ? ($cond2 ? throw new Exception() : "b") : "c";', 'DevStrict.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }
}
