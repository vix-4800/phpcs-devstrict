<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Functions;

use DevStrict\Tests\BaseTest;

/**
 * Tests for PreferJsonValidateSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class PreferJsonValidateSniffTest extends BaseTest
{
    /**
     * Test that json_last_error triggers warning.
     */
    public function testJsonLastErrorTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = json_decode($json);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Invalid JSON");
}', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode followed by json_last_error triggers warning.
     */
    public function testJsonDecodeWithLastErrorCheckTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
json_decode($json);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Invalid JSON");
}', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode with assignment does not trigger (data is used).
     */
    public function testJsonDecodeWithAssignmentAndUsageDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php
$data = json_decode($json);
echo $data->name;', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertNoViolations($result);
    }

    /**
     * Test that method calls are ignored.
     */
    public function testMethodCallsAreIgnored(): void
    {
        $result = $this->runPhpcs('<?php
$obj->json_decode($json);
if ($obj->json_last_error() !== 0) {
    throw new Exception();
}', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertNoViolations($result);
    }

    /**
     * Test that static method calls are ignored.
     */
    public function testStaticMethodCallsAreIgnored(): void
    {
        $result = $this->runPhpcs('<?php
SomeClass::json_decode($json);
SomeClass::json_last_error();', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple json_last_error calls trigger warnings.
     */
    public function testMultipleJsonLastErrorCallsTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
json_decode($json1);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception();
}

json_decode($json2);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception();
}', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertContainsWarning($result, 'json_validate()');
        // Should have multiple warnings
        $warningCount = substr_count($result, 'json_validate()');
        $this->assertGreaterThanOrEqual(2, $warningCount);
    }

    /**
     * Test that json_decode with JSON_THROW_ON_ERROR triggers warning.
     */
    public function testJsonDecodeWithThrowOnErrorTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
json_decode($json, true, 512, JSON_THROW_ON_ERROR);', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode with JSON_THROW_ON_ERROR in try-catch triggers warning.
     */
    public function testJsonDecodeWithThrowOnErrorInTryCatchTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
try {
    json_decode(\'{test}\', true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    throw new Exception("Invalid JSON");
}', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode with JSON_THROW_ON_ERROR and assignment still triggers warning if value not used.
     */
    public function testJsonDecodeWithThrowOnErrorAndUnusedAssignmentTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
try {
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    // Just validating
}', 'DevStrict.Functions.PreferJsonValidate');

        $this->assertContainsWarning($result, 'json_validate()');
    }
}
