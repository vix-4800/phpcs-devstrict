<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Yii2;

use DevStrict\Tests\BaseTest;

/**
 * Tests for DisallowResponseFormatAssignmentSniff.
 */
class DisallowResponseFormatAssignmentSniffTest extends BaseTest
{
    /**
     * Test that direct assignment to Yii::$app->response->format triggers a warning.
     */
    public function testResponseFormatAssignmentTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

class TestController
{
    public function actionTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ["status" => "ok"];
    }
}', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that direct assignment with different constant triggers a warning.
     */
    public function testResponseFormatWithDifferentConstantTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

Yii::$app->response->format = Response::FORMAT_XML;', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that direct assignment with string literal triggers a warning.
     */
    public function testResponseFormatWithStringLiteralTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
Yii::$app->response->format = "json";', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that using $this->asJson() does not trigger warning.
     */
    public function testAsJsonMethodDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
class TestController
{
    public function actionTest()
    {
        return $this->asJson(["status" => "ok"]);
    }
}', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertNoViolations($result);
    }

    /**
     * Test that reading Yii::$app->response->format does not trigger warning.
     */
    public function testReadingResponseFormatDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$format = Yii::$app->response->format;
if (Yii::$app->response->format === "json") {
    echo "JSON format";
}', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertNoViolations($result);
    }

    /**
     * Test that other Yii::$app->response properties do not trigger warning.
     */
    public function testOtherResponsePropertiesDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
Yii::$app->response->statusCode = 404;
Yii::$app->response->headers->set("X-Custom", "value");', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertNoViolations($result);
    }

    /**
     * Test that assignment with whitespace and newlines triggers warning.
     */
    public function testResponseFormatWithWhitespaceTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

Yii :: $app -> response -> format = Response::FORMAT_JSON;', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that variable or method named Yii does not trigger warning.
     */
    public function testVariableNamedYiiDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$Yii = new stdClass();
$Yii->app->response->format = "json";

class Test {
    public function Yii() {
        return null;
    }
}', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple violations in the same file.
     */
    public function testMultipleViolations(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

class TestController
{
    public function action1()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ["status" => "ok"];
    }

    public function action2()
    {
        Yii::$app->response->format = Response::FORMAT_XML;
        return ["status" => "ok"];
    }
}', 'DevStrict.Yii2.DisallowResponseFormatAssignment');

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
        // Should contain warnings for both assignments
        $warningCount = substr_count($result, 'Yii::$app->response->format');
        $this->assertGreaterThanOrEqual(2, $warningCount);
    }
}
