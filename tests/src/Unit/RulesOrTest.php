<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesOrTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Engine\RulesStateInterface;
use Drupal\rules\Plugin\RulesExpression\RulesOr;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesOr
 * @group rules
 */
class RulesOrTest extends RulesUnitTestBase {

  /**
   * The 'or' condition container being tested.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionContainerInterface
   */
  protected $or;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->or = new RulesOr([], '', [], $this->expressionManager->reveal());
  }

  /**
   * Tests one condition.
   */
  public function testOneCondition() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->executeWithState(
      Argument::type(RulesStateInterface::class))->shouldBeCalledTimes(1);

    $this->or->addExpressionObject($this->trueConditionExpression->reveal());
    $this->assertTrue($this->or->execute(), 'Single condition returns TRUE.');
  }

  /**
   * Tests an empty OR.
   */
  public function testEmptyOr() {
    $property = new \ReflectionProperty($this->or, 'conditions');
    $property->setAccessible(TRUE);

    $this->assertEmpty($property->getValue($this->or));
    $this->assertTrue($this->or->execute(), 'Empty OR returns TRUE.');
  }

  /**
   * Tests two true condition.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->executeWithState(
      Argument::type(RulesStateInterface::class))->shouldBeCalledTimes(1);

    $this->or
      ->addExpressionObject($this->trueConditionExpression->reveal())
      ->addExpressionObject($this->trueConditionExpression->reveal());

    $this->assertTrue($this->or->execute(), 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called twice.
    $this->falseConditionExpression->executeWithState(
      Argument::type(RulesStateInterface::class))->shouldBeCalledTimes(2);

    $this->or
      ->addExpressionObject($this->falseConditionExpression->reveal())
      ->addExpressionObject($this->falseConditionExpression->reveal());

    $this->assertFalse($this->or->execute(), 'Two false conditions return FALSE.');
  }

}
