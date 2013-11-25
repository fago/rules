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
  public function testActionExecution() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $or = new RulesOr();
    $or->condition($this->trueCondition);
    $result = $or->execute();
    $this->assertTrue($result, 'Single condition returns TRUE.');
  }
}
