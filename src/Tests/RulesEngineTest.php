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
    $rule->addCondition($this->createCondition('rules_test_true'))
      ->addCondition($this->createCondition('rules_test_true'))
      ->addCondition($this->createExpression('rules_or')
        ->addCondition($this->createCondition('rules_test_true')->negate())
        ->addCondition($this->createCondition('rules_test_false'))
        ->addCondition($this->createExpression('rules_and')
          ->addCondition($this->createCondition('rules_test_false'))
          ->addCondition($this->createCondition('rules_test_true')->negate())
          ->negate()));
    $rule->addAction($this->createAction('rules_test_log'));
    $rule->execute();
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

}
