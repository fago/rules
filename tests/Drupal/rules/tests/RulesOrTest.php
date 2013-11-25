<?php

/**
 * @file
 * Contains Drupal\rules\tests\RulesOrTest.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\rules\RulesOr;

/**
 * Tests the rules OR condition plugin.
 */
class RulesOrTest extends RulesTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'RulesOr class tests',
      'description' => 'Test the RuleOr class',
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
    $or = new RulesOr(array(), 'test', array());
    $or->condition($this->trueCondition);
    $result = $or->execute();
    $this->assertTrue($result, 'Single condition returns TRUE.');
  }

  /**
   * Test an empty OR.
   */
  public function testemptyOr() {
    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr(array(), 'test', array());
    $result = $or->execute();
    $this->assertTrue($result, 'Empty OR returns TRUE.');
  }

  /**
   * Tests two true condition.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr(array(), 'test', array());
    $or->condition($this->trueCondition);
    $or->condition($this->trueCondition);
    $result = $or->execute();
    $this->assertTrue($result, 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseCondition->expects($this->exactly(2))
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr(array(), 'test', array());
    $or->condition($this->falseCondition);
    $or->condition($this->falseCondition);
    $result = $or->execute();
    $this->assertFalse($result, 'Two false conditions return FALSE.');
  }

  /**
   * Tests one negated condition.
   */
  public function testNegatedCondition() {
    // The method on the test condition must be called once.
    $this->negatedTrueCondition->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr(array(), 'test', array());
    $or->condition($this->negatedTrueCondition);
    $result = $or->execute();
    $this->assertFalse($result, 'Negated condition returns FALSE.');
  }

  /**
   * Tests one negated FALSE condition.
   */
  public function testNegatedFalseCondition() {
    // The method on the test condition must be called once.
    $this->negatedFalseCondition->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr(array(), 'test', array());
    $or->condition($this->negatedFalseCondition);
    $result = $or->execute();
    $this->assertTrue($result, 'Negated false condition returns TRUE.');
  }
}
