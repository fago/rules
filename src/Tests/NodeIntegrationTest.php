<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\NodeIntegrationTest.
 */

namespace Drupal\rules\Tests;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Engine\RulesLog;

/**
 * Tests the rules engine with node integration
 */
class NodeIntegrationTest extends RulesDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'field', 'text', 'entity', 'user'];

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Node integration',
      'description' => 'Test using the Rules API with nodes.',
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

    $this->installSchema('system', array('sequences'));
    $this->installEntitySchema('user');
  }

  /**
   * Tests that a complex data selector can be applied to nodes.
   */
  public function testNodeDataSelector() {
    $entity_manager = $this->container->get('entity.manager');
    $node_type = $entity_manager->getStorage('node_type')
      ->create(['type' => 'page']);
    $node_type->save();
    $node = $entity_manager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);
    $user = $entity_manager->getStorage('user')
      ->create([
        'name' => 'test value',
      ]);
    $user->save();
    $node->setOwner($user);

    $rule = $this->createRulesRule(['context_definitions' => [
      'node' => new ContextDefinition('entity:node', 'Node'),
    ]]);

    // Test that the long detailed data selector works.
    $rule->addCondition($this->rulesExpressionManager->createInstance('rules_condition', [
      'condition_id' => 'rules_test_string_condition',
      'context_mapping' => ['text:select' => 'node:uid:0:entity:name:0:value'],
    ]));
    // Test that the shortened data selector without list indices.
    $rule->addCondition($this->rulesExpressionManager->createInstance('rules_condition', [
      'condition_id' => 'rules_test_string_condition',
      'context_mapping' => ['text:select' => 'node:uid:entity:name:value'],
    ]));

    $rule->addAction($this->createRulesAction('rules_test_log'));
    $rule->setContextValue('node', $node);

    $rule->execute();

    // Test that the action logged something.
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

}
