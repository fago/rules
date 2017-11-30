<?php

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Core\ConditionManager;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Engine\ExecutionState;

/**
 * Test using the Rules API to create and evaluate rules.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class RulesEngineTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * Tests creating a rule and iterating over the rule elements.
   */
  public function testRuleCreation() {
    // Create an 'and' condition container and add conditions to it.
    $and = $this->expressionManager->createAnd()
      ->addCondition('rules_test_false')
      ->addCondition('rules_test_true', ContextConfig::create()->negateResult())
      ->negate();

    // Test that the 'and' condition container evaluates to TRUE.
    $this->assertTrue($and->execute());

    // Create an 'or' condition container and add conditions to it, including
    // the previously created 'and' condition container.
    $or = $this->expressionManager->createOr()
      ->addCondition('rules_test_true', ContextConfig::create()->negateResult())
      ->addCondition('rules_test_false')
      ->addExpressionObject($and);

    // Test that the 'or' condition container evaluates to TRUE.
    $this->assertTrue($or->execute());

    // Create a rule and add conditions to it, including the previously created
    // 'or' condition container.
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true')
      ->addCondition('rules_test_true')
      ->addExpressionObject($or);

    // Test that the rule's condition container evaluates to TRUE.
    $this->assertTrue($rule->getConditions()->execute());

    // Add an action to it and execute the rule.
    $rule->addAction('rules_test_log');
    $rule->execute();

    // Test that the action logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Tests passing a string context to a condition.
   */
  public function testContextPassing() {
    $rule = $this->expressionManager->createRule();

    $rule->addCondition('rules_test_string_condition', ContextConfig::create()
      ->map('text', 'test')
    );
    $rule->addAction('rules_test_log');

    RulesComponent::create($rule)
      ->addContextDefinition('test', ContextDefinition::create('string'))
      ->setContextValue('test', 'test value')
      ->execute();

    // Test that the action logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Tests that a condition can provide a value and another one can consume it.
   */
  public function testProvidedVariables() {
    $rule = $this->expressionManager->createRule();

    // The first condition provides a "provided_text" variable.
    $rule->addCondition('rules_test_provider');
    // The second condition consumes the variable.
    $rule->addCondition('rules_test_string_condition', ContextConfig::create()
      ->map('text', 'provided_text')
    );

    $rule->addAction('rules_test_log');

    $component = RulesComponent::create($rule);

    $violations = $component->checkIntegrity();
    $this->assertEquals(0, iterator_count($violations));

    $component->execute();
    // Test that the action logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Tests that provided variables can be renamed with configuration.
   */
  public function testRenamingOfProvidedVariables() {
    $rule = $this->expressionManager->createRule();

    // The condition provides a "provided_text" variable.
    $rule->addCondition('rules_test_provider', ContextConfig::create()
      ->provideAs('provided_text', 'newname')
    );

    $state = ExecutionState::create();
    $rule->executeWithState($state);

    // Check that the newly named variable exists and has the provided value.
    $variable = $state->getVariable('newname');
    $this->assertEqual($variable->getValue(), 'test value');
  }

  /**
   * Tests that multiple actions can consume and provide context variables.
   */
  public function testActionProvidedContext() {
    // @todo: Convert the test to make use of actions instead of conditions.
    $rule = $this->expressionManager->createRule();

    // The condition provides a "provided_text" variable.
    $rule->addCondition('rules_test_provider');

    // The action provides a "concatenated" variable.
    $rule->addAction('rules_test_string', ContextConfig::create()
      ->map('text', 'provided_text')
    );

    // Add the same action again which will provide a "concatenated2" variable
    // now.
    $rule->addAction('rules_test_string', ContextConfig::create()
      ->map('text', 'concatenated')
      ->provideAs('concatenated', 'concatenated2')
    );

    $state = ExecutionState::create();
    $rule->executeWithState($state);

    // Check that the created variables exists and have the provided values.
    $concatenated = $state->getVariable('concatenated');
    $this->assertEqual($concatenated->getValue(), 'test valuetest value');
    $concatenated2 = $state->getVariable('concatenated2');
    $this->assertEqual($concatenated2->getValue(), 'test valuetest valuetest valuetest value');
  }

  /**
   * Verifies swapping out core services works.
   */
  public function testSwappedCoreServices() {
    $condition_manager = $this->container->get('plugin.manager.condition');
    $this->assertTrue($condition_manager instanceof ConditionManager);
  }

}
