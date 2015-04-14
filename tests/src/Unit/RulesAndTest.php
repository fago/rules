<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesAndTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Plugin\RulesExpression\RulesAnd;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesAnd
 * @group rules
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

    $this->and = new RulesAnd([], '', [], $this->expressionManager);
  }

  /**
   * Tests one condition.
   */
  public function testOneCondition() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->expects($this->once())
      ->method('executeWithState');

    $this->and->addExpressionObject($this->trueConditionExpression);
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
    // The method on the test condition must be called once.
    $this->trueConditionExpression->expects($this->exactly(2))
      ->method('executeWithState');

    $this->and
      ->addExpressionObject($this->trueConditionExpression)
      ->addExpressionObject($this->trueConditionExpression);

    $this->assertTrue($this->and->execute(), 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseConditionExpression->expects($this->once())
      ->method('executeWithState');

    $this->and
      ->addExpressionObject($this->falseConditionExpression)
      ->addExpressionObject($this->falseConditionExpression);

    $this->assertFalse($this->and->execute(), 'Two false conditions return FALSE.');
  }
}
