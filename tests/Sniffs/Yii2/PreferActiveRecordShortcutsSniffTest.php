<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Yii2;

use DevStrict\Tests\BaseTest;

/**
 * Tests for PreferActiveRecordShortcutsSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class PreferActiveRecordShortcutsSniffTest extends BaseTest
{
    /**
     * Test that find()->where()->one() triggers a warning.
     */
    public function testFindWhereOneTrigersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class User extends \yii\db\ActiveRecord
{
    public static function getById($id)
    {
        return self::find()->where(["id" => $id])->one();
    }
}', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findOne() shortcut method');
    }

    /**
     * Test that find()->where()->all() triggers a warning.
     */
    public function testFindWhereAllTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class User extends \yii\db\ActiveRecord
{
    public static function getByStatus($status)
    {
        return self::find()->where(["status" => $status])->all();
    }
}', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findAll() shortcut method');
    }

    /**
     * Test that Model::find()->where()->one() triggers a warning.
     */
    public function testStaticCallWithWhereOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->where(["email" => $email])->one();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findOne() shortcut method');
    }

    /**
     * Test that $model->find()->where()->all() triggers a warning.
     */
    public function testInstanceCallWithWhereAllTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = $model->find()->where(["active" => true])->all();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findAll() shortcut method');
    }

    /**
     * Test that find()->where()->andWhere()->one() does NOT trigger a warning.
     * This is a complex query that cannot be replaced with findOne().
     */
    public function testComplexChainWithOneDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()
    ->where(["status" => 1])
    ->andWhere(["role" => "admin"])
    ->one();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->orderBy()->all() does NOT trigger a warning.
     * This is a complex query that cannot be replaced with findAll().
     */
    public function testChainWithOrderByAndAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::find()
    ->where(["status" => 1])
    ->orderBy("created_at DESC")
    ->all();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that findOne() does not trigger a warning.
     */
    public function testFindOneDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::findOne($id);', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that findAll() does not trigger a warning.
     */
    public function testFindAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::findAll(["status" => 1]);', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->one() without where() does not trigger a warning.
     */
    public function testFindOneWithoutWhereDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->one();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->all() without where() does not trigger a warning.
     */
    public function testFindAllWithoutWhereDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::find()->all();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where() without one()/all() does not trigger a warning.
     */
    public function testFindWhereWithoutTerminatorDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$query = User::find()->where(["status" => 1]);', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->count() does not trigger a warning.
     */
    public function testFindWhereCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$count = User::find()->where(["status" => 1])->count();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->exists() does not trigger a warning.
     */
    public function testFindWhereExistsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$exists = User::find()->where(["email" => $email])->exists();', 'DevStrict.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }
}
