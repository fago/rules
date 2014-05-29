<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesOrTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Plugin\RulesExpression\RulesOr;

/**
 * Tests the rules OR condition plugin.
 */
class RulesOrTest extends RulesTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'RulesOr class tests',
      'description' => 'Test the RuleOr class',
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

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr([], 'test', []);
    $or->addCondition($this->trueCondition);
    $result = $or->execute();
    $this->assertTrue($result, 'Single condition returns TRUE.');
  }

  /**
   * Tests an empty OR.
   */
  public function testemptyOr() {
    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr([], 'test', []);
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
    $or = new RulesOr([], 'test', []);
    $or->addCondition($this->trueCondition);
    $or->addCondition($this->trueCondition);
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
    $or = new RulesOr([], 'test', []);
    $or->addCondition($this->falseCondition);
    $or->addCondition($this->falseCondition);
    $result = $or->execute();
    $this->assertFalse($result, 'Two false conditions return FALSE.');
  }
}
