<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Formatting;

use DevStrict\Tests\BaseTest;

/**
 * Tests for DisallowConsecutivePhpTagsSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowConsecutivePhpTagsSniffTest extends BaseTest
{
    private const SNIFF = 'DevStrict.Formatting.DisallowConsecutivePhpTags';

    public function testDetectsMultipleConsecutiveTagSwitches(): void
    {
        $code = '<?php if ($a) : ?>
    <?= $output ?>
<?php endif; ?>
<?php if ($b) : ?>
    <?= $output2 ?>
<?php endif; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'consecutive PHP tag switches');
    }

    public function testAllowsSingleTagSwitch(): void
    {
        $code = '<?php if ($condition) : ?>
    <div>HTML content</div>
<?php endif; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDetectsThreeConsecutiveSwitches(): void
    {
        $code = '<?php echo $a; ?>
<?php echo $b; ?>
<?php echo $c; ?>
<?php echo $d; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'consecutive PHP tag switches');
    }

    public function testAllowsTagsWithRealHtmlBetween(): void
    {
        $code = '<?php echo $header; ?>
<div class="content">
    <p>Some real HTML content here</p>
</div>
<?php echo $footer; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDetectsShortEchoTagSwitches(): void
    {
        $code = '<?php $a = 1; ?>
<?= $a ?>
<?php $b = 2; ?>
<?= $b ?>
<?php $c = 3; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'consecutive PHP tag switches');
    }

    public function testReportsOnlyOncePerSequence(): void
    {
        $code = '<?php $a = 1; ?>
<?php $b = 2; ?>
<?php $c = 3; ?>
<?php $d = 4; ?>
<?php $e = 5; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        // Should contain exactly one warning, not multiple
        $this->assertContainsWarning($result, 'consecutive PHP tag switches');

        // Count WARNING occurrences - should be 1
        $warningCount = substr_count($result, 'WARNING');
        $this->assertSame(1, $warningCount, 'Expected exactly one warning for the sequence');
    }

    public function testAllowsPurePhpFile(): void
    {
        $code = '<?php

declare(strict_types=1);

class MyClass
{
    public function method(): void
    {
        echo "Hello";
    }
}
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDetectsAlternativeSyntaxSwitches(): void
    {
        $code = '<?php if ($a) : ?>
<?php endif; ?>
<?php if ($b) : ?>
<?php endif; ?>
<?php if ($c) : ?>
<?php endif; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'consecutive PHP tag switches');
    }

    public function testAllowsNewlinesWithWhitespaceOnly(): void
    {
        // This should still trigger because there's only whitespace between tags
        $code = '<?php $a = 1; ?>

<?php $b = 2; ?>

<?php $c = 3; ?>
';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'consecutive PHP tag switches');
    }
}
