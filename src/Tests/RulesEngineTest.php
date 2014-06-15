<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesEngineTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesLog;

/**
 * Tests the rules engine functionality.
 */
class RulesEngineTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Rules engine tests',
      'description' => 'Test using the Rules API to create and evaluate rules.',
      'group' => 'Rules',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Clear the log from any stale entries that are bleeding over from previous
    // tests.
    $logger = RulesLog::logger();
    $logger->clear();
  }

  /**
   * Tests creating a rule and iterating over the rule elements.
   */
  public function testRuleCreation() {
    // Create an 'and' condition container and add conditions to it.
    $and = $this->createRulesAnd()
      ->addCondition($this->createRulesCondition('rules_test_false'))
      ->addCondition($this->createRulesCondition('rules_test_true')->negate())
      ->negate();

    // Test that the 'and' condition container evaluates to TRUE.
    $this->assertTrue($and->execute());

    // Create an 'or' condition container and add conditions to it, including
    // the previously created 'and' condition container.
    $or = $this->createRulesOr()
      ->addCondition($this->createRulesCondition('rules_test_true')->negate())
      ->addCondition($this->createRulesCondition('rules_test_false'))
      ->addCondition($and);

    // Test that the 'or' condition container evaluates to TRUE.
    $this->assertTrue($or->execute());

    // Create a rule and add conditions to it, including the previously created
    // 'or' condition container.
    $rule = $this->createRulesRule();
    $rule->addCondition($this->createRulesCondition('rules_test_true'))
      ->addCondition($this->createRulesCondition('rules_test_true'))
      ->addCondition($or);

    // Test that the rule's condition container evaluates to TRUE.
    $this->assertTrue($rule->getConditions()->execute());

    // Add an action to it and execute the rule.
    $rule->addAction($this->createRulesAction('rules_test_log'));
    $rule->execute();

    // Test that the action logged something.
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

  /**
   * Tests passing a string context to a condition.
   */
  public function testContextPassing() {
    $rule = $this->createRulesRule(array(
      'context_definitions' => array(
        'test' => ContextDefinition::create($this->typedDataManager, 'string')
          ->setLabel('Test string'),
      )
    ));

    $rule->addCondition($this->rulesExpressionManager->createInstance('rules_condition', array(
      'condition_id' => 'rules_test_string_condition',
      'parameter_mapping' => array(
        'text:select' => 'test',
      ),
    )));

    $rule->addAction($this->createRulesAction('rules_test_log'));
    $rule->setContextValue('test', 'test value');

    $rule->execute();

    // Test that the action logged something.
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

}
