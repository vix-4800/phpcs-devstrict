<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Objects;

use DevStrict\Tests\BaseTest;

/**
 * Tests for DisallowVariableStaticPropertySniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowVariableStaticPropertySniffTest extends BaseTest
{
    public function testVariableStaticPropertyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

$toast = $model::$toast_array[$model->toast];
', 'DevStrict.Objects.DisallowVariableStaticProperty');

        $this->assertContainsError($result, 'Static properties must be accessed via a class name');
    }

    public function testParenthesizedVariableStaticPropertyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

$toast = ($model)::$toast_array[$model->toast];
', 'DevStrict.Objects.DisallowVariableStaticProperty');

        $this->assertContainsError($result, 'Static properties must be accessed via a class name');
    }

    public function testClassNameAccessIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

$toast = User::$toast_array[$id];
', 'DevStrict.Objects.DisallowVariableStaticProperty');

        $this->assertNoViolations($result);
    }

    public function testSelfAccessIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function getFoo(): int
    {
        return self::$foo;
    }
}
', 'DevStrict.Objects.DisallowVariableStaticProperty');

        $this->assertNoViolations($result);
    }
}
