<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesEngineTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Engine\RulesLog;

/**
 * Tests the rules engine functionality.
 */
class RulesEngineTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Rules engine tests',
      'description' => 'Test using the Rules API to create and evaluate rules.',
      'group' => 'Rules',
    );
  }

  /**
   * Tests creating a rule and iterating over the rule elements.
   */
  public function testRuleCreation() {
    $rule = $this->createRule();
    $rule->condition($this->createCondition('rules_test_true'))
      ->condition($this->createCondition('rules_test_true'))
      ->condition($this->createExpression('rules_or')
        ->condition($this->createCondition('rules_test_true')->negate())
        ->condition($this->createCondition('rules_test_false'))
        ->condition($this->createExpression('rules_and')
          ->condition($this->createCondition('rules_test_false'))
          ->condition($this->createCondition('rules_test_true')->negate())
          ->negate()));
    $rule->action($this->createAction('rules_test_log'));
    $rule->execute();
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

}
