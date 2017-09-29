<?php

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Engine\ConditionExpressionInterface;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Plugin\RulesExpression\RulesAnd;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesAnd
 * @group Rules
 */
class RulesAndTest extends RulesUnitTestBase {

  /**
   * The 'and' condition container being tested.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionContainerInterface
   */
  protected $and;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->and = new RulesAnd([], '', [], $this->expressionManager->reveal());
  }

  /**
   * Tests one condition.
   */
  public function testOneCondition() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);
    $this->and->addExpressionObject($this->trueConditionExpression->reveal());
    $this->assertTrue($this->and->execute(), 'Single condition returns TRUE.');
  }

  /**
   * Tests an empty AND.
   */
  public function testEmptyAnd() {
    $property = new \ReflectionProperty($this->and, 'conditions');
    $property->setAccessible(TRUE);

    $this->assertEmpty($property->getValue($this->and));
    $this->assertFalse($this->and->execute(), 'Empty AND returns FALSE.');
  }

  /**
   * Tests two true conditions.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called twice.
    $this->trueConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $second_condition = $this->prophesize(ConditionExpressionInterface::class);
    $second_condition->getUuid()->willReturn('true_uuid2');

    $second_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(1);

    $this->and
      ->addExpressionObject($this->trueConditionExpression->reveal())
      ->addExpressionObject($second_condition->reveal());

    $this->assertTrue($this->and->execute(), 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $second_condition = $this->prophesize(ConditionExpressionInterface::class);
    $second_condition->getUuid()->willReturn('false_uuid2');

    $second_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(FALSE)
      ->shouldNotBeCalled();

    $this->and
      ->addExpressionObject($this->falseConditionExpression->reveal())
      ->addExpressionObject($second_condition->reveal());

    $this->assertFalse($this->and->execute(), 'Two false conditions return FALSE.');
  }

}
