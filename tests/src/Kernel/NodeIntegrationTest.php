<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\NodeIntegrationTest.
 */

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;

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
  public static $modules = ['node', 'field', 'text', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

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
        'node' => ContextDefinition::create('entity:node')
          ->setLabel('Node')
          ->toArray(),
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
    // @see \Drupal\rules_test\Plugin\RulesAction\TestNodeAction
    $action = $this->expressionManager->createAction('rules_test_node')
      ->setConfiguration([
        'context_definitions' => [
          'node' => ContextDefinition::create('entity:node')
            ->setLabel('Node')
            ->toArray(),
          'title' => ContextDefinition::create('string')
            ->setLabel('Title')
            ->toArray(),
        ],
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

  /**
   * Tests that tokens in action parameters get replaced.
   */
  public function testTokenReplacements() {
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
        'name' => 'klausi',
      ]);

    $user->save();
    $node->setOwner($user);

    // Configure a simple rule with one action.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->map('message', 'message')
        ->map('type', 'type')
        ->process('message', 'rules_tokens')
        ->setConfigKey('action_id', 'rules_system_message')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'node' => ContextDefinition::create('entity:node')->toArray(),
        'message' => ContextDefinition::create('string')->toArray(),
        'type' => ContextDefinition::create('string')->toArray(),
      ],
    ]);
    $rule->setContextValue('node', $node);
    $rule->setContextValue('message', 'Hello [node:uid:entity:name:value]!');
    $rule->setContextValue('type', 'status');
    $rule->addExpressionObject($action);
    $rule->execute();

    $messages = drupal_set_message();
    $this->assertEqual((string) $messages['status'][0], 'Hello klausi!');
  }

  /**
   * Tests that date formatting tokens on node fields get replaced.
   */
  public function testDateTokens() {
    $entity_manager = $this->container->get('entity.manager');
    $entity_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    $node = $entity_manager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
        // Set the created date to the first second in 1970.
        'created' => 1,
      ]);

    // Configure a simple rule with one action.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->map('message', 'message')
        ->map('type', 'type')
        ->process('message', 'rules_tokens')
        ->setConfigKey('action_id', 'rules_system_message')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'node' => ContextDefinition::create('entity:node')->toArray(),
        'message' => ContextDefinition::create('string')->toArray(),
        'type' => ContextDefinition::create('string')->toArray(),
      ],
    ]);
    $rule->setContextValue('node', $node);
    $rule->setContextValue('message', 'The node was created in the year [node:created:custom:Y]');
    $rule->setContextValue('type', 'status');
    $rule->addExpressionObject($action);
    $rule->execute();

    $messages = drupal_set_message();
    $this->assertEqual((string) $messages['status'][0], 'The node was created in the year 1970');
  }

  /**
   * Tests that the data set action works on nodes.
   */
  public function testDataSet() {
    $entity_manager = $this->container->get('entity.manager');
    $entity_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    $node = $entity_manager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);

    // Configure a simple rule with one action.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->setConfigKey('action_id', 'rules_data_set')
        ->map('data', 'node:title')
        ->map('value', 'new_title')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'node' => ContextDefinition::create('entity:node')->toArray(),
        'new_title' => ContextDefinition::create('string')->toArray(),
      ],
    ]);
    $rule->setContextValue('node', $node);
    $rule->setContextValue('new_title', 'new title');
    $rule->addExpressionObject($action);
    $rule->execute();

    $this->assertEqual('new title', $node->getTitle());
    $this->assertNotNull($node->id(), 'Node ID is set, which means that the node has been auto-saved.');
  }

}
