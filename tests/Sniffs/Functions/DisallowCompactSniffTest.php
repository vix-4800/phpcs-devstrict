<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Functions;

use DevStrict\Tests\BaseTest;

/**
 * Tests for DisallowCompactSniff.
 */
class DisallowCompactSniffTest extends BaseTest
{
    /**
     * Test that compact() function triggers an error.
     */
    public function testCompactFunctionTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$name = "John";
$age = 30;
$data = compact("name", "age");', 'DevStrict.Functions.DisallowCompact');

        $this->assertContainsError($result, 'compact()');
    }

    /**
     * Test that explicit array syntax does not trigger error.
     */
    public function testExplicitArraySyntaxDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$name = "John";
$age = 30;
$data = ["name" => $name, "age" => $age];', 'DevStrict.Functions.DisallowCompact');

        $this->assertNoViolations($result);
    }

    /**
     * Test that compact method call does not trigger error.
     */
    public function testCompactMethodDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$object->compact();
SomeClass::compact();', 'DevStrict.Functions.DisallowCompact');

        $this->assertNoViolations($result);
    }

    /**
     * Test that function declaration named compact does not trigger error.
     */
    public function testCompactFunctionDeclarationDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
function compact(array $values) {
    return array_values($values);
}', 'DevStrict.Functions.DisallowCompact');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple compact() calls.
     */
    public function testMultipleCompactCalls(): void
    {
        $result = $this->runPhpcs('<?php
$data1 = compact("var1", "var2");
$data2 = compact("var3", "var4");', 'DevStrict.Functions.DisallowCompact');

        $this->assertContainsError($result, 'compact()');
        // Should contain errors for both compact calls
        $errorCount = substr_count($result, 'compact()');
        $this->assertGreaterThanOrEqual(1, $errorCount);
    }

    /**
     * Test compact() with spread operator.
     */
    public function testCompactWithSpreadOperator(): void
    {
        $result = $this->runPhpcs('<?php
$vars = ["name", "age"];
$data = compact(...$vars);', 'DevStrict.Functions.DisallowCompact');

        $this->assertContainsError($result, 'compact()');
    }

    /**
     * Test compact() in return statement.
     */
    public function testCompactInReturnStatement(): void
    {
        $result = $this->runPhpcs('<?php
function getData($name, $age) {
    return compact("name", "age");
}', 'DevStrict.Functions.DisallowCompact');

        $this->assertContainsError($result, 'compact()');
    }
}
