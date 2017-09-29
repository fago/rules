<?php

namespace Drupal\Tests\rules\Unit\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * Test the integrity check functionality during configuration time.
 *
 * @group Rules
 */
class IntegrityCheckTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests that the integrity check can be invoked.
   */
  public function testIntegrityCheck() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->checkIntegrity();
    $this->assertEquals(0, iterator_count($violation_list));
  }

  /**
   * Tests that a wrongly configured variable name triggers a violation.
   */
  public function testUnknownVariable() {
    $rule = $this->rulesExpressionManager->createRule();
    $action = $this->rulesExpressionManager->createAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'unknown_variable')
      ->toArray()
    );
    $rule->addExpressionObject($action);

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $violation = $violation_list[0];
    $this->assertEquals(
      'Data selector <em class="placeholder">unknown_variable</em> for context <em class="placeholder">Entity</em> is invalid. Unable to get variable unknown_variable, it is not defined.',
      (string) $violation->getMessage()
    );
    $this->assertEquals($action->getUuid(), $violation->getUuid());
  }

  /**
   * Tests that the integrity check with UUID works.
   */
  public function testCheckUuid() {
    $rule = $this->rulesExpressionManager->createRule();
    // Just use a rule with 2 dummy actions.
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'unknown_variable_1'));
    $second_action = $this->rulesExpressionManager->createAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'unknown_variable_2')
      ->toArray()
    );
    $rule->addExpressionObject($second_action);

    $all_violations = RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->checkIntegrity();

    $this->assertEquals(2, iterator_count($all_violations));

    $uuid_violations = $all_violations->getFor($second_action->getUuid());
    $this->assertEquals(1, count($uuid_violations));
    $violation = $uuid_violations[0];
    $this->assertEquals(
      'Data selector <em class="placeholder">unknown_variable_2</em> for context <em class="placeholder">Entity</em> is invalid. Unable to get variable unknown_variable_2, it is not defined.',
      (string) $violation->getMessage()
    );
    $this->assertEquals($second_action->getUuid(), $violation->getUuid());
  }

  /**
   * Tests that an invalid condition plugin ID results in a violation.
   */
  public function testInvalidCondition() {
    $rule = $this->rulesExpressionManager->createRule();
    $condition = $this->rulesExpressionManager->createCondition('invalid_condition_id');
    $rule->addExpressionObject($condition);

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Condition plugin <em class="placeholder">invalid_condition_id</em> does not exist', (string) $violations[0]->getMessage());
    $this->assertEquals($condition->getUuid(), $violations[0]->getUuid());
  }

  /**
   * Tests that a missing condition plugin ID results in a violation.
   */
  public function testMissingCondition() {
    $rule = $this->rulesExpressionManager->createRule();
    $condition = $this->rulesExpressionManager->createCondition('');
    $rule->addExpressionObject($condition);

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Condition plugin ID is missing', (string) $violations[0]->getMessage());
    $this->assertEquals($condition->getUuid(), $violations[0]->getUuid());
  }

  /**
   * Tests that an invalid action plugin ID results in a violation.
   */
  public function testInvalidAction() {
    $rule = $this->rulesExpressionManager->createRule();
    $action = $this->rulesExpressionManager->createAction('invalid_action_id');
    $rule->addExpressionObject($action);

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Action plugin <em class="placeholder">invalid_action_id</em> does not exist', (string) $violations[0]->getMessage());
    $this->assertEquals($action->getUuid(), $violations[0]->getUuid());
  }

  /**
   * Tests that a missing action plugin ID results in a violation.
   */
  public function testMissingAction() {
    $rule = $this->rulesExpressionManager->createRule();
    $action = $this->rulesExpressionManager->createAction('');
    $rule->addExpressionObject($action);

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Action plugin ID is missing', (string) $violations[0]->getMessage());
    $this->assertEquals($action->getUuid(), $violations[0]->getUuid());
  }

  /**
   * Tests invalid characters in provided variables.
   */
  public function testInvalidProvidedName() {
    $rule = $this->rulesExpressionManager->createRule();

    // The condition provides a "provided_text" variable.
    $condition = $this->rulesExpressionManager->createCondition('rules_test_provider', ContextConfig::create()
      ->provideAs('provided_text', 'invalid_näme')
      ->toArray()
    );
    $rule->addExpressionObject($condition);

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $this->assertEquals(
      'Provided variable name <em class="placeholder">invalid_näme</em> contains not allowed characters.',
      (string) $violation_list[0]->getMessage()
    );
    $this->assertEquals($condition->getUuid(), $violation_list[0]->getUuid());
  }

  /**
   * Tests the input restriction on contexts.
   */
  public function testInputRestriction() {
    $rule = $this->rulesExpressionManager->createRule();

    $action = $this->rulesExpressionManager->createAction('rules_entity_fetch_by_id', ContextConfig::create()
      // The entity type must be configured as value, so this provokes the
      // violation.
      ->map('type', 'variable_1')
      ->setValue('entity_id', 1)
      ->toArray()
    );
    $rule->addExpressionObject($action);

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('variable_1', ContextDefinition::create('string'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $this->assertEquals(
      'The context <em class="placeholder">Entity type</em> may not be configured using a selector.',
      (string) $violation_list[0]->getMessage()
    );
    $this->assertEquals($action->getUuid(), $violation_list[0]->getUuid());
  }

  /**
   * Tests the data selector restriction on contexts.
   */
  public function testSelectorRestriction() {
    $rule = $this->rulesExpressionManager->createRule();

    $action = $this->rulesExpressionManager->createAction('rules_data_set', ContextConfig::create()
      // Setting a data value is only possible with a selector, this will
      // trigger the violation.
      ->setValue('data', 'some value')
      ->setValue('value', 'some new value')
      ->toArray()
    );
    $rule->addExpressionObject($action);

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $this->assertEquals(
      'The context <em class="placeholder">Data</em> may only be configured using a selector.',
      (string) $violation_list[0]->getMessage()
    );
    $this->assertEquals($action->getUuid(), $violation_list[0]->getUuid());
  }

  /**
   * Tests that a primitive context is assigned something that matches.
   */
  public function testPrimitiveTypeViolation() {
    $rule = $this->rulesExpressionManager->createRule();

    // The condition expects a string but we pass a list, which will trigger the
    // violation.
    $condition = $this->rulesExpressionManager->createCondition('rules_test_string_condition', ContextConfig::create()
      ->map('text', 'list_variable')
      ->toArray()
    );
    $rule->addExpressionObject($condition);

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('list_variable', ContextDefinition::create('list'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $this->assertEquals(
      'Expected a string data type for context <em class="placeholder">Text to compare</em> but got a list data type instead.',
      (string) $violation_list[0]->getMessage()
    );
    $this->assertEquals($condition->getUuid(), $violation_list[0]->getUuid());
  }

  /**
   * Tests that a list context is assigned something that matches.
   */
  public function testListTypeViolation() {
    $rule = $this->rulesExpressionManager->createRule();

    // The condition expects a list for the type context but we pass a node
    // which will trigger the violation.
    $condition = $this->rulesExpressionManager->createCondition('rules_node_is_of_type', ContextConfig::create()
      ->map('node', 'node')
      ->map('types', 'node')
      ->toArray()
    );
    $rule->addExpressionObject($condition);

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $this->assertEquals(
      'Expected a list data type for context <em class="placeholder">Content types</em> but got a entity:node data type instead.',
      (string) $violation_list[0]->getMessage()
    );
    $this->assertEquals($condition->getUuid(), $violation_list[0]->getUuid());
  }

  /**
   * Tests that a complex data context is assigned something that matches.
   */
  public function testComplexTypeViolation() {
    $rule = $this->rulesExpressionManager->createRule();

    // The condition expects a node context but gets a list instead which cause
    // the violation.
    $condition = $this->rulesExpressionManager->createCondition('rules_node_is_of_type', ContextConfig::create()
      ->map('node', 'list_variable')
      ->map('types', 'list_variable')
      ->toArray()
    );
    $rule->addExpressionObject($condition);

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('list_variable', ContextDefinition::create('list'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $this->assertEquals(
      'Expected a entity:node data type for context <em class="placeholder">Node</em> but got a list data type instead.',
      (string) $violation_list[0]->getMessage()
    );
    $this->assertEquals($condition->getUuid(), $violation_list[0]->getUuid());
  }

  /**
   * Tests that an absent required context triggers a violation.
   */
  public function testMissingRequiredContext() {
    $rule = $this->rulesExpressionManager->createRule();

    // The condition is completely un-configured, missing 2 required contexts.
    $condition = $this->rulesExpressionManager->createCondition('rules_node_is_of_type');
    $rule->addExpressionObject($condition);

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(2, iterator_count($violation_list));
    $this->assertEquals(
      'The required context <em class="placeholder">Node</em> is missing.',
      (string) $violation_list[0]->getMessage()
    );
    $this->assertEquals(
      'The required context <em class="placeholder">Content types</em> is missing.',
      (string) $violation_list[1]->getMessage()
    );
    $this->assertEquals($condition->getUuid(), $violation_list[0]->getUuid());
    $this->assertEquals($condition->getUuid(), $violation_list[1]->getUuid());
  }

  /**
   * Make sure that nested expression violations have the correct UUID.
   */
  public function testNestedExpressionUuids() {
    $rule = $this->rulesExpressionManager->createRule();
    $action_set = $this->rulesExpressionManager->createInstance('rules_action_set');
    // The most inner action will trigger a violation for an unknown variable.
    $action = $this->rulesExpressionManager->createAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'unknown_variable')
      ->toArray()
    );
    $action_set->addExpressionObject($action);
    $rule->addExpressionObject($action_set);

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    // UUID must be that of the most inner action.
    $this->assertEquals($action->getUuid(), $violation_list[0]->getUuid());
  }

  /**
   * Tests using provided variables in sub-sequent actions passes checks.
   */
  public function testUsingProvidedVariables() {
    $rule = $this->rulesExpressionManager->createRule();

    $rule->addAction('rules_variable_add', ContextConfig::create()
      ->setValue('type', 'any')
      ->setValue('value', 'foo')
    );
    $rule->addAction('rules_variable_add', ContextConfig::create()
      ->setValue('type', 'any')
      ->map('value', 'variable_added')
    );

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(0, iterator_count($violation_list));
  }

  /**
   * Tests that refined context is respected when checking context.
   */
  public function testRefinedContextViolation() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_variable_add', ContextConfig::create()
      ->setValue('type', 'integer')
      ->map('value', 'text')
    );

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('text', ContextDefinition::create('string'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
  }

  /**
   * Tests context can be refined based upon mapped context.
   */
  public function testRefiningContextBasedonMappedContext() {
    // DataComparision condition refines context based on selected data. Thus
    // it for the test and ensure checking integrity passes when the comparison
    // value is of a compatible type and fails else.
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addCondition('rules_data_comparison', ContextConfig::create()
      ->map('data', 'text')
      ->map('value', 'text2')
    );

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('text', ContextDefinition::create('string'))
      ->addContextDefinition('text2', ContextDefinition::create('string'))
      ->checkIntegrity();
    $this->assertEquals(0, iterator_count($violation_list));

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('text', ContextDefinition::create('string'))
      ->addContextDefinition('text2', ContextDefinition::create('integer'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
  }

  /**
   * Tests using provided variables with refined context.
   */
  public function testUsingRefinedProvidedVariables() {
    $rule = $this->rulesExpressionManager->createRule();

    $rule->addAction('rules_variable_add', ContextConfig::create()
      ->setValue('type', 'string')
      ->setValue('value', 'foo')
    );
    $rule->addAction('rules_system_message', ContextConfig::create()
      ->map('message', 'variable_added')
      ->setValue('type', 'status')
    );
    // The message action requires a string, thus if the context is not refined
    // it will end up as "any" and integrity check would fail.
    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(0, iterator_count($violation_list));
  }

}
