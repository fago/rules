<?php

namespace Drupal\Tests\rules\Unit\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\ExecutionMetadataState;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * Tests that the setup of the execution metadata state for an expression works.
 *
 * @group Rules
 */
class PrepareExecutionMetadataStateTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests that a variable can be added by an action and is then available.
   */
  public function testAddingVariable() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_variable_add', ContextConfig::create()
      ->setValue('type', 'string')
      ->setValue('value', '')
      ->provideAs('variable_added', 'result')
    );

    $state = ExecutionMetadataState::create();
    $found = $rule->prepareExecutionMetadataState($state);
    $this->assertTrue($state->hasDataDefinition('result'));
    $this->assertNull($found);
  }

  /**
   * Tests partial state setup until an expression is reached in the tree.
   */
  public function testPreparingUntil() {
    // Setup a rule with 2 actions.
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_variable_add', ContextConfig::create()
      ->setValue('type', 'string')
      ->setValue('value', '')
      ->provideAs('variable_added', 'result1')
    );
    $second_action = $this->rulesExpressionManager->createAction('rules_variable_add')
      ->setConfiguration(ContextConfig::create()
        ->setValue('type', 'string')
        ->setValue('value', '')
        ->provideAs('variable_added', 'result2')
        ->toArray()
      );
    $rule->addExpressionObject($second_action);

    $state = ExecutionMetadataState::create();
    // Preparing the state until the second action means the variable of the
    // first action is available, but the second is not yet.
    $found = $rule->prepareExecutionMetadataState($state, $second_action);
    $this->assertTrue($state->hasDataDefinition('result1'));
    $this->assertFalse($state->hasDataDefinition('result2'));
    $this->assertTrue($found);
  }

  /**
   * Tests that state preparation also works for actions in a loop.
   */
  public function testPrepareInLoop() {
    $rule = $this->rulesExpressionManager->createRule();

    $loop = $this->rulesExpressionManager->createInstance('rules_loop', ['list' => 'string_list']);
    $action = $this->rulesExpressionManager->createAction('rules_test_string')
      ->setConfiguration(ContextConfig::create()
        ->setValue('text', 'x')
        ->toArray()
      );
    $loop->addExpressionObject($action);

    $rule->addExpressionObject($loop);

    $state = RulesComponent::create($rule)
      ->addContextDefinition('string_list', ContextDefinition::create('string')->setMultiple())
      ->getMetadataState();

    $found = $rule->prepareExecutionMetadataState($state, $action);
    $this->assertTrue($state->hasDataDefinition('list_item'));
    $this->assertTrue($found);
  }

  /**
   * Tests that the loop list item is removed after the loop.
   */
  public function testPrepareAfterLoop() {
    $rule = $this->rulesExpressionManager->createRule();

    $loop = $this->rulesExpressionManager->createInstance('rules_loop', ['list' => 'string_list']);
    $action = $this->rulesExpressionManager->createAction('rules_test_string')
      ->setConfiguration(ContextConfig::create()
        ->setValue('text', 'x')
        ->toArray()
      );
    $loop->addExpressionObject($action);

    $rule->addExpressionObject($loop);

    $state = RulesComponent::create($rule)
      ->addContextDefinition('string_list', ContextDefinition::create('string')->setMultiple())
      ->getMetadataState();

    $found = $rule->prepareExecutionMetadataState($state);
    $this->assertFalse($state->hasDataDefinition('list_item'));
    $this->assertNull($found);
  }

}
