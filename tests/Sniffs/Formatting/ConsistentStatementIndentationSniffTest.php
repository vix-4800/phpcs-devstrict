<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Formatting;

use DevStrict\Tests\BaseTest;

/**
 * Tests for ConsistentStatementIndentationSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class ConsistentStatementIndentationSniffTest extends BaseTest
{
    private const SNIFF = 'DevStrict.Formatting.ConsistentStatementIndentation';

    public function testDetectsInconsistentEchoIndentation(): void
    {
        $code = '<?php
function render() {
    Modal::begin([
        "id" => "modal",
    ]);
        echo "<img src=\"test.jpg\">";
    Modal::end();
}
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Statement indentation is inconsistent');
    }

    public function testAllowsConsistentIndentation(): void
    {
        $code = '<?php
function render() {
    Modal::begin([
        "id" => "modal",
    ]);
    echo "<img src=\"test.jpg\">";
    Modal::end();
}
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDetectsInconsistentIfIndentation(): void
    {
        $code = '<?php
function process() {
    $a = 1;
        if ($a > 0) {
        echo "positive";
    }
    return $a;
}
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Statement indentation is inconsistent');
    }

    public function testAllowsNestedBlocks(): void
    {
        $code = '<?php
function process() {
    if (true) {
        echo "nested";
        if (false) {
            echo "more nested";
        }
    }
    echo "back to base";
}
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testIgnoresClosuresWithDifferentLevels(): void
    {
        $code = '<?php
$callback = function () {
    echo "inside closure";
};
echo "outside closure";
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDetectsInconsistentReturnIndentation(): void
    {
        $code = '<?php
function getValue() {
    $value = calculate();
        return $value;
}
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Statement indentation is inconsistent');
    }

    public function testAllowsProperlyIndentedLoops(): void
    {
        $code = '<?php
function iterate() {
    foreach ($items as $item) {
        echo $item;
    }
    for ($i = 0; $i < 10; $i++) {
        echo $i;
    }
    while (true) {
        break;
    }
}
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }
}
