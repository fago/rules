<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ContextIntegrationTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Exception\RulesEvaluationException;

/**
 * Tests the the extended core context API with Rules.
 *
 * @group rules
 */
class ContextIntegrationTest extends RulesDrupalTestBase {

  /**
   * Tests that a required context mapping that is NULL throws an exception.
   */
  public function testRequiredNullMapping() {
    // Configure a simple rule with one action.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->setConfigKey('action_id', 'rules_test_string')
        ->map('text', 'null_context')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'null_context' => ContextDefinition::create('string')->toArray(),
      ],
    ]);
    $rule->setContextValue('null_context', NULL);
    $rule->addExpressionObject($action);

    try {
      $rule->execute();
      $this->fail('No exception thrown when required context value is NULL');
    }
    catch (RulesEvaluationException $e) {
      $this->pass('Exception thrown as expected when a required context is NULL');
    }
  }

  /**
   * Tests that a required context value that is NULL throws an exception.
   */
  public function testRequiredNullValue() {
    // Configure a simple rule with one action. The required 'text' context is
    // set to be NULL.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->setConfigKey('action_id', 'rules_test_string')
        ->setValue('text', NULL)
        ->toArray()
    );

    $rule = $this->expressionManager->createRule([]);
    $rule->addExpressionObject($action);
    try {
      $rule->execute();
      $this->fail('No exception thrown when required context value is NULL');
    }
    catch (RulesEvaluationException $e) {
      $this->pass('Exception thrown as expected when a required context is NULL');
    }
  }

  /**
   * Tests that NULL values for contexts are allowed if specified.
   */
  public function testAllowNullValue() {
    // Configure a simple rule with the data set action which allows NULL
    // values.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->setConfigKey('action_id', 'rules_data_set')
        ->map('data', 'null_variable')
        ->map('value', 'new_value')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'null_variable' => ContextDefinition::create('string')->toArray(),
        'new_value' => ContextDefinition::create('string')->toArray(),
      ],
    ]);
    $rule->setContextValue('null_variable', NULL);
    $rule->setContextValue('new_value', 'new value');
    $rule->addExpressionObject($action);
    $rule->execute();

    $this->assertEqual('new value', $rule->getContextValue('null_variable'));
  }

}
