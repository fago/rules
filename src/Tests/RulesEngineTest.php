<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesEngineTest.
 */

namespace Drupal\rules\Tests;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Engine\RulesLog;
use Drupal\rules\Engine\RulesState;

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
    $rule = $this->createRulesRule(['context_definitions' => [
      'test' => new ContextDefinition('string', t('Test string')),
    ]]);

    $rule->addCondition($this->rulesExpressionManager->createInstance('rules_condition', [
      'condition_id' => 'rules_test_string_condition',
      'context_mapping' => ['text:select' => 'test'],
    ]));

    $rule->addAction($this->createRulesAction('rules_test_log'));
    $rule->setContextValue('test', 'test value');

    $rule->execute();

    // Test that the action logged something.
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

  /**
   * Tests that a condition can provide a value and another one can consume it.
   */
  public function testProvidedVariables() {
    $rule = $this->createRulesRule();

    // The first condition provides a "provided_text" variable.
    $rule->addCondition($this->rulesExpressionManager->createInstance('rules_condition', [
      'condition_id' => 'rules_test_provider',
    ]));
    // The secound condition consumes the variable.
    $rule->addCondition($this->rulesExpressionManager->createInstance('rules_condition', [
      'condition_id' => 'rules_test_string_condition',
      'context_mapping' => ['text:select' => 'provided_text'],
    ]));

    $rule->addAction($this->createRulesAction('rules_test_log'));
    $rule->execute();

    // Test that the action logged something.
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

  /**
   * Tests that provided variables can be renamed with configuration.
   */
  public function testRenamingOfProvidedVariables() {
    $rule = $this->createRulesRule();

    // The condition provides a "provided_text" variable.
    $rule->addCondition($this->rulesExpressionManager->createInstance('rules_condition', [
      'condition_id' => 'rules_test_provider',
      // Expose the variable as 'newname'.
      'provides_mapping' => ['provided_text' => 'newname'],
    ]));

    $state = new RulesState();
    $rule->executeWithState($state);
    // Check that the newly named variable exists and has the provided value.
    $variable = $state->getVariable('newname');
    $this->assertEqual($variable->getContextValue(), 'test value');
  }

}
