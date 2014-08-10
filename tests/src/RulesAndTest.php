<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesAndTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Plugin\RulesExpression\RulesAnd;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesAnd
 * @group rules
 */
class RulesAndTest extends RulesUnitTestBase {

  /**
   * The 'and' condition container being tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionContainerInterface
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
    $this->trueCondition->expects($this->once())
      ->method('executeWithState');

    $this->and->addCondition($this->trueCondition);
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
   * Tests two true condition.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->exactly(2))
      ->method('executeWithState');

    $this->and->addCondition($this->trueCondition)
      ->addCondition($this->trueCondition);

    $this->assertTrue($this->and->execute(), 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseCondition->expects($this->once())
      ->method('executeWithState');

    $this->and->addCondition($this->falseCondition)
      ->addCondition($this->falseCondition);

    $this->assertFalse($this->and->execute(), 'Two false conditions return FALSE.');
  }
}
