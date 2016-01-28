<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\IntegrityCheckTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * Test the integrity check functionality during configuration time.
 *
 * @group rules
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
    $this->assertEquals(iterator_count($violation_list), 0);
  }

  /**
   * Tests that a wrongly configured variable name triggers a violation.
   */
  public function testUnknownVariable() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'unknown_variable')
    );

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(iterator_count($violation_list), 1);
    $violation = $violation_list[0];
    $this->assertEquals(
      'Data selector <em class="placeholder">unknown_variable</em> for context <em class="placeholder">Entity</em> is invalid. Unable to get variable unknown_variable, it is not defined.',
      (string) $violation->getMessage()
    );
  }

  /**
   * Tests that the integrity check with UUID works.
   */
  public function testCheckUuid() {
    $rule = $this->rulesExpressionManager->createRule();
    // Just use a rule with 2 dummy actions.
    $rule->addAction('rules_entity_save', ContextConfig::create()
          ->map('entity', 'unknown_variable_1'))
        ->addAction('rules_entity_save', ContextConfig::create()
          ->map('entity', 'unknown_variable_2'));

    $all_violations = RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->checkIntegrity();

    $this->assertEquals(2, iterator_count($all_violations));

    // Get the UUID of the second action.
    $iterator = $rule->getIterator();
    $iterator->next();
    $uuid = $iterator->key();

    $uuid_violations = $all_violations->getFor($uuid);
    $this->assertEquals(1, count($uuid_violations));
    $violation = $uuid_violations[0];
    $this->assertEquals(
      'Data selector <em class="placeholder">unknown_variable_2</em> for context <em class="placeholder">Entity</em> is invalid. Unable to get variable unknown_variable_2, it is not defined.',
      (string) $violation->getMessage()
    );
  }

  /**
   * Tests that an invalid condition plugin ID results in a violation.
   */
  public function testInvalidCondition() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addCondition('invalid_condition_id');

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Condition plugin <em class="placeholder">invalid_condition_id</em> does not exist', (string) $violations[0]->getMessage());
  }

  /**
   * Tests that a missing condition plugin ID results in a violation.
   */
  public function testMissingCondition() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addCondition('');

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Condition plugin ID is missing', (string) $violations[0]->getMessage());
  }

  /**
   * Tests that an invalid action plugin ID results in a violation.
   */
  public function testInvalidAction() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('invalid_action_id');

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Action plugin <em class="placeholder">invalid_action_id</em> does not exist', (string) $violations[0]->getMessage());
  }

  /**
   * Tests that a missing action plugin ID results in a violation.
   */
  public function testMissingAction() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('');

    $violations = RulesComponent::create($rule)->checkIntegrity();
    $this->assertEquals(1, iterator_count($violations));
    $this->assertEquals('Action plugin ID is missing', (string) $violations[0]->getMessage());
  }

  /**
   * Tests invalid characters in provided variables.
   */
  public function testInvalidProvidedName() {
    $rule = $this->rulesExpressionManager->createRule();

    // The condition provides a "provided_text" variable.
    $rule->addCondition('rules_test_provider', ContextConfig::create()
      ->provideAs('provided_text', 'invalid_näme')
    );

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(iterator_count($violation_list), 1);
    $this->assertEquals(
      'Provided variable name <em class="placeholder">invalid_näme</em> contains not allowed characters.',
      (string) $violation_list[0]->getMessage()
    );
  }

  /**
   * Tests the input restrction on contexts.
   */
  public function testInputRestriction() {
    $rule = $this->rulesExpressionManager->createRule();

    $rule->addAction('rules_entity_fetch_by_id', ContextConfig::create()
      // The entity type must be configured as value, so this provokes the
      // violation.
      ->map('type', 'variable_1')
      ->setValue('entity_id', 1)
    );

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('variable_1', ContextDefinition::create('string'))
      ->checkIntegrity();
    $this->assertEquals(iterator_count($violation_list), 1);
    $this->assertEquals(
      'The context <em class="placeholder">Entity type</em> may not be configured using a selector.',
      (string) $violation_list[0]->getMessage()
    );
  }

  /**
   * Tests the data selector restriction on contexts.
   */
  public function testSelectorRestriction() {
    $rule = $this->rulesExpressionManager->createRule();

    $rule->addAction('rules_data_set', ContextConfig::create()
      // Setting a data value is only possible with a selector, this will
      // trigger the violation.
      ->setValue('data', 'some value')
      ->setValue('value', 'some new value')
    );

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(iterator_count($violation_list), 1);
    $this->assertEquals(
      'The context <em class="placeholder">Data</em> may only be configured using a selector.',
      (string) $violation_list[0]->getMessage()
    );
  }

}
