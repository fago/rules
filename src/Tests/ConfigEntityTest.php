<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ConfigEntityTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Engine\RulesLog;

/**
 * Tests storage and loading of Rules config entities.
 *
 * @group rules
 */
class ConfigEntityTest extends RulesDrupalTestBase {

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
   * Tests that an empty rule configuration can be saved.
   */
  public function testSavingEmptyRule() {
    $entity_manager = $this->container->get('entity.manager');
    $config_entity = $entity_manager->getStorage('rule')
      ->create(['id' => 'test_rule']);
    $config_entity->save();
  }

  /**
   * Tests saving the configuration of an action and then loading it again.
   */
  public function testConfigAction() {
    $action = $this->rulesExpressionManager->createInstance('rules_action', [
      'action_id' => 'rules_test_log',
    ]);

    $entity_manager = $this->container->get('entity.manager');
    $config_entity = $entity_manager->getStorage('rule')
      ->create([
        'id' => 'test_rule',
        'expression_id' => 'rules_action',
        'configuration' => $action->getConfiguration(),
      ]);
    $config_entity->save();

    $loaded_entity = $entity_manager->getStorage('rule')->load('test_rule');
    $this->assertEqual($loaded_entity->get('expression_id'), 'rules_action', 'Expression ID was successfully loaded.');
    $this->assertEqual($loaded_entity->get('configuration'), $action->getConfiguration(), 'Action configuration is the same after loading the config.');

    // Create the Rules expression object from the configuration.
    $expression = $loaded_entity->getExpression();
    $expression->execute();

    // Test that the action logged something.
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

}
