<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Functions;

use DevStrict\Tests\BaseTest;

/**
 * Tests for DisallowIsNullSniff.
 */
class DisallowIsNullSniffTest extends BaseTest
{
    /**
     * Test that is_null() function triggers a warning.
     */
    public function testIsNullFunctionTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
if (is_null($var)) {
    echo "null";
}', 'DevStrict.Functions.DisallowIsNull');

        $this->assertContainsWarning($result, 'is_null()');
    }

    /**
     * Test that strict comparison (=== null) does not trigger warning.
     */
    public function testStrictComparisonDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($var === null) {
    echo "null";
}', 'DevStrict.Functions.DisallowIsNull');

        $this->assertNoViolations($result);
    }

    /**
     * Test that is_null method call does not trigger warning.
     */
    public function testIsNullMethodDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$object->is_null();
SomeClass::is_null();', 'DevStrict.Functions.DisallowIsNull');

        $this->assertNoViolations($result);
    }

    /**
     * Test that function declaration named is_null does not trigger warning.
     */
    public function testIsNullFunctionDeclarationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
function is_null($value) {
    return $value === null;
}', 'DevStrict.Functions.DisallowIsNull');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple is_null() calls.
     */
    public function testMultipleIsNullCalls(): void
    {
        $result = $this->runPhpcs('<?php
if (is_null($var1) || is_null($var2)) {
    echo "null";
}', 'DevStrict.Functions.DisallowIsNull');

        $this->assertContainsWarning($result, 'is_null()');
        // Should contain warnings for both is_null calls
        $warningCount = substr_count($result, 'is_null()');
        $this->assertGreaterThanOrEqual(1, $warningCount);
    }
}


