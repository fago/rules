<?php

/**
 * @file
 * Contains Drupal\rules\tests\RulesAndTest.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\rules\RulesAnd;

/**
 * Tests the rules AND condition plugin.
 */
class RulesAndTest extends RulesTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'RulesAnd class tests',
      'description' => 'Test the RuleAnd class',
      'group' => 'Rules',
    );
  }

  /**
   * Tests one condition.
   */
  public function testOneCondition() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $and = new RulesAnd(array(), 'test', array());
    $and->condition($this->trueCondition);
    $result = $and->execute();
    $this->assertTrue($result, 'Single condition returns TRUE.');
  }

  /**
   * Test an empty AND.
   */
  public function testemptyAnd() {
    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $and = new RulesAnd(array(), 'test', array());
    $result = $and->execute();
    $this->assertFalse($result, 'Empty AND returns FALSE.');
  }

  /**
   * Tests two true condition.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->exactly(2))
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $and = new RulesAnd(array(), 'test', array());
    $and->condition($this->trueCondition);
    $and->condition($this->trueCondition);
    $result = $and->execute();
    $this->assertTrue($result, 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseCondition->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $and = new RulesAnd(array(), 'test', array());
    $and->condition($this->falseCondition);
    $and->condition($this->falseCondition);
    $result = $and->execute();
    $this->assertFalse($result, 'Two false conditions return FALSE.');
  }
}
