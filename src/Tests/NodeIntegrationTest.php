<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\NodeIntegrationTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Engine\RulesLog;

/**
 * Test using the Rules API with nodes.
 *
 * @group rules
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
  public function setUp() {
    parent::setUp();

    // Clear the log from any stale entries that are bleeding over from previous
    // tests.
    $logger = RulesLog::logger();
    $logger->clear();

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
  }

  /**
   * Tests that a complex data selector can be applied to nodes.
   */
  public function testNodeDataSelector() {
    $entity_manager = $this->container->get('entity.manager');
    $entity_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

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

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'node' => [
          'type' => 'entity:node',
          'label' => 'Node',
        ],
      ],
    ]);

    // Test that the long detailed data selector works.
    $rule->addCondition('rules_test_string_condition', ContextConfig::create()
      ->map('text', 'node:uid:0:entity:name:0:value')
    );

    // Test that the shortened data selector without list indices.
    $rule->addCondition('rules_test_string_condition', ContextConfig::create()
      ->map('text', 'node:uid:entity:name:value')
    );

    $rule->addAction('rules_test_log');
    $rule->setContextValue('node', $node);
    $rule->execute();

    // Test that the action logged something.
    $log = RulesLog::logger()->get();
    $this->assertEqual($log[0][0], 'action called');
  }

  /**
   * Tests that a node is automatically saved after being changed in an action.
   */
  public function testNodeAutoSave() {
    $entity_manager = $this->container->get('entity.manager');
    $entity_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    $node = $entity_manager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);

    // We use the rules_test_node action plugin which marks its node context for
    // auto saving.
    // @see \Drupal\rules_test\Plugin\Action\TestNodeAction
    $action = $this->expressionManager->createAction('rules_test_node')
    ->setConfiguration([
      'context_definitions' => [
        'node' => [
          'type' => 'entity:node',
          'label' => 'Node',
        ],
        'title' => [
          'type' => 'string',
          'label' => 'Title',
        ],
      ]
    ] + ContextConfig::create()
        ->map('node', 'node')
        ->map('title', 'title')
        ->toArray()
    );

    $action->setContextValue('node', $node);
    $action->setContextValue('title', 'new title');
    $action->execute();

    $this->assertNotNull($node->id(), 'Node ID is set, which means that the node has been saved.');
  }

}
