<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\EventHandlerTest.
 */

namespace Drupal\Tests\rules\Kernel;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\rules\Context\ContextConfig;

/**
 * Tests events with qualified name.
 *
 * @group rules
 */
class EventHandlerTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rules', 'rules_test', 'system', 'node', 'field', 'user'];

  /**
   * The entity storage for Rules config entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * A node used for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['field']);

    $this->storage = $this->container->get('entity_type.manager')->getStorage('rules_reaction_rule');

    $entity_type_manager = $this->container->get('entity_type.manager');
    $entity_type_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    FieldStorageConfig::create([
      'field_name' => 'field_integer',
      'type' => 'integer',
      'entity_type' => 'node',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_integer',
      'entity_type' => 'node',
      'bundle' => 'page',
    ])->save();

    $this->node = $entity_type_manager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);
  }

  /**
   * Tests EventHandlerEntityBundle configuration.
   */
  public function testEntityBundleHandlerConfiguration() {
    // Create rule with an action 'rules_entity_presave:node–-page'.
    $rule = $this->expressionManager->createRule();
    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'expression_id' => 'rules_rule',
      'event' => 'rules_entity_presave:node-–page',
      'configuration' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // @todo Add integrity check that node.field_integer is detected by Rules.
  }

  /**
   * Tests EventHandlerEntityBundle execution.
   */
  public function testEntityBundleHandlerExecution() {
    $this->node->field_integer->setValue(['0' => 1, '1' => 2]);

    // Create rule with an action 'rules_entity_presave:node--page'.
    $rule = $this->expressionManager->createRule();
    $rule->addAction('rules_test_log',
      ContextConfig::create()
        ->map('message', 'node.field_integer.0.value')
    );
    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'expression_id' => 'rules_rule',
      'event' => 'rules_entity_presave:node--page',
      'configuration' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // @todo Better dispatch presave event.
    $this->node->save();

    // Test that the action in the rule logged node value.
    $this->assertRulesLogEntryExists('1');
  }

}
