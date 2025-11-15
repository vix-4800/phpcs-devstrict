<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Yii2;

use DevStrict\Tests\BaseTest;

/**
 * Tests for PreferMagicPropertiesSniff.
 */
class PreferMagicPropertiesSniffTest extends BaseTest
{
    /**
     * Test that Yii::$app->user->getId() triggers a warning.
     */
    public function testUserGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$userId = Yii::$app->user->getId();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "id" instead of getter method "getId()"');
    }

    /**
     * Test that Yii::$app->user->getIdentity() triggers a warning.
     */
    public function testUserGetIdentityTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$identity = Yii::$app->user->getIdentity();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "identity" instead of getter method "getIdentity()"');
    }

    /**
     * Test that chained getter calls trigger warnings.
     */
    public function testChainedGettersTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$userId = Yii::$app->user->getIdentity()->getId();', 'DevStrict.Yii2.PreferMagicProperties');

        // Both getIdentity() and getId() should trigger warnings
        $this->assertContainsWarning($result, 'getIdentity()');
        $this->assertContainsWarning($result, 'getId()');
    }

    /**
     * Test that $model->getName() triggers a warning.
     */
    public function testModelGetNameTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$name = $model->getName();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "name" instead of getter method "getName()"');
    }

    /**
     * Test that $model->getTitle() triggers a warning.
     */
    public function testModelGetTitleTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$title = $model->getTitle();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "title" instead of getter method "getTitle()"');
    }

    /**
     * Test that $model->getIsActive() triggers a warning.
     */
    public function testModelGetIsActiveTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$isActive = $model->getIsActive();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "isActive" instead of getter method "getIsActive()"');
    }

    /**
     * Test that boolean getters with 'is' prefix trigger warnings.
     */
    public function testIsGetterTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$isGuest = Yii::$app->user->isGuest();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "isGuest" instead of getter method "isGuest()"');
    }

    /**
     * Test that boolean getters with 'can' prefix trigger warnings.
     */
    public function testCanGetterTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$canEdit = $model->canEdit();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "canEdit" instead of getter method "canEdit()"');
    }

    /**
     * Test that custom getter triggers a warning.
     */
    public function testCustomGetterTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$fullName = $model->getFullName();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'Use magic property "fullName" instead of getter method "getFullName()"');
    }

    /**
     * Test that getter with arguments does NOT trigger a warning.
     */
    public function testGetterWithArgumentsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$url = $model->getUrl($scheme);', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test that getter with multiple arguments does NOT trigger a warning.
     */
    public function testGetterWithMultipleArgumentsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$value = $model->getValue($key, $default);', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test that using property directly does NOT trigger a warning.
     */
    public function testPropertyAccessDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$userId = Yii::$app->user->id;
$identity = Yii::$app->user->identity;
$name = $model->name;', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test that method definition does NOT trigger a warning.
     */
    public function testMethodDefinitionDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

class User
{
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test that static method call does NOT trigger a warning.
     */
    public function testStaticMethodCallDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$value = SomeClass::getName();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test that function call does NOT trigger a warning.
     */
    public function testFunctionCallDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$name = getName();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test that setter method does NOT trigger a warning.
     */
    public function testSetterMethodDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$model->setName("John");
$model->setValue($data);', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test that very short getter names do NOT trigger a warning.
     */
    public function testShortGetterNamesDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$x = $model->get();
$y = $model->is();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple getters in one expression.
     */
    public function testMultipleGettersInExpression(): void
    {
        $result = $this->runPhpcs('<?php

$result = $model->getName() . " " . $model->getTitle();', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'getName()');
        $this->assertContainsWarning($result, 'getTitle()');
    }

    /**
     * Test getter in conditional.
     */
    public function testGetterInConditional(): void
    {
        $result = $this->runPhpcs('<?php

if ($model->getStatus() === "active") {
    // do something
}', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'getStatus()');
    }

    /**
     * Test getter in array.
     */
    public function testGetterInArray(): void
    {
        $result = $this->runPhpcs('<?php

$data = [
    "id" => $model->getId(),
    "name" => $model->getName(),
];', 'DevStrict.Yii2.PreferMagicProperties');

        $this->assertContainsWarning($result, 'getId()');
        $this->assertContainsWarning($result, 'getName()');
    }
}
