<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Formatting;

use DevStrict\Tests\BaseTest;

/**
 * Tests for MethodChainingIndentationSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class MethodChainingIndentationSniffTest extends BaseTest
{
    public function testFirstChainedCallMustBeIndented(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
->where(["id" => $model->user_id])
    ->select(["id"])
    ->all();
', 'DevStrict.Formatting.MethodChainingIndentation');

        $this->assertContainsError($result, 'First chained call must be indented');
    }

    public function testSubsequentCallsMustAlign(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
    ->where(["id" => $model->user_id])
      ->select(["id"])
    ->all();
', 'DevStrict.Formatting.MethodChainingIndentation');

        $this->assertContainsError($result, 'Chained call indentation must match');
    }

    public function testNestedStructuresKeepIndentation(): void
    {
        $result = $this->runPhpcs('<?php

function example(): array
{
    return User::find()
        ->where([
            "id" => $id,
        ])
        ->limit(10)
        ->all();
}
', 'DevStrict.Formatting.MethodChainingIndentation');

        $this->assertNoViolations($result);
    }

    public function testInlineChainIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

User::find()->where(["id" => $model->user_id])->all();
', 'DevStrict.Formatting.MethodChainingIndentation');

        $this->assertNoViolations($result);
    }
}
