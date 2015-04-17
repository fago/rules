<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ConfigEntityTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests storage and loading of Rules config entities.
 *
 * @group rules
 */
class ConfigEntityTest extends RulesDrupalTestBase {

  /**
   * The entity storage for Rules config entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->storage = $this->container->get('entity.manager')->getStorage('rules_component');
  }

  /**
   * Tests that an empty rule configuration can be saved.
   */
  public function testSavingEmptyRule() {
    $rule = $this->expressionManager->createRule();
    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'expression_id' => 'rules_rule',
      'configuration' => $rule->getConfiguration(),
    ]);
    $config_entity->save();
  }

  /**
   * Tests saving the configuration of an action and then loading it again.
   */
  public function testConfigAction() {
    $action = $this->expressionManager->createAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'expression_id' => 'rules_action',
      'configuration' => $action->getConfiguration(),
    ]);
    $config_entity->save();

    $loaded_entity = $this->storage->load('test_rule');
    $this->assertEqual($loaded_entity->get('expression_id'), 'rules_action', 'Expression ID was successfully loaded.');
    $this->assertEqual($loaded_entity->get('configuration'), $action->getConfiguration(), 'Action configuration is the same after loading the config.');

    // Create the Rules expression object from the configuration.
    $expression = $loaded_entity->getExpression();
    $expression->execute();

    // Test that the action logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Tests saving the nested config of a rule and then loading it again.
   */
  public function testConfigRule() {
    // Create a simple rule with one action and one condition.
    $rule = $this->expressionManager
      ->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'expression_id' => 'rules_rule',
      'configuration' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    $loaded_entity = $this->storage->load('test_rule');
    // Create the Rules expression object from the configuration.
    $expression = $loaded_entity->getExpression();
    $expression->execute();

    // Test that the action logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Make sure that expressions using context definitions can be exported.
   */
  public function testContextDefinitionExport() {
    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'test' => [
          'type' => 'string',
          'label' => 'Test string',
        ],
      ],
    ]);

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'expression_id' => 'rules_rule',
      'configuration' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    $loaded_entity = $this->storage->load('test_rule');
    // Create the Rules expression object from the configuration.
    $expression = $loaded_entity->getExpression();
    $context_definitions = $expression->getContextDefinitions();
    $this->assertEqual($context_definitions['test']->getDataType(), 'string', 'Data type of context definition is correct.');
    $this->assertEqual($context_definitions['test']->getLabel(), 'Test string', 'Label of context definition is correct.');
  }

}
