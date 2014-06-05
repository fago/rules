<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesAndTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests the rules AND condition plugin.
 */
class RulesAndTest extends RulesTestBase {

  /**
   * A mocked 'and' condition container.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\RulesAnd
   */
  protected $testRulesAnd;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'RulesAnd class tests',
      'description' => 'Test the RuleAnd class',
      'group' => 'Rules',
    ];
  }

  /**
   * Tests one condition.
   */
  public function testOneCondition() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->once())
      ->method('execute');

    $and = $this->getMockAnd()
      ->addCondition($this->trueCondition);

    $this->assertTrue($and->execute(), 'Single condition returns TRUE.');
  }

  /**
   * Tests an empty AND.
   */
  public function testEmptyAnd() {
    $and = $this->getMockAnd();
    $this->assertFalse($and->execute(), 'Empty AND returns FALSE.');
  }

  /**
   * Tests two true condition.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->exactly(2))
      ->method('execute');

    $and = $this->getMockAnd()
      ->addCondition($this->trueCondition)
      ->addCondition($this->trueCondition);

    $this->assertTrue($and->execute(), 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseCondition->expects($this->once())
      ->method('execute');

    $and = $this->getMockAnd()
      ->addCondition($this->falseCondition)
      ->addCondition($this->falseCondition);

    $this->assertFalse($and->execute(), 'Two false conditions return FALSE.');
  }
}
