<?php

namespace Drupal\Tests\rules\Kernel;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Event\EntityEvent;

/**
 * Tests events with qualified name.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class ConfigurableEventHandlerTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rules', 'system', 'node', 'field', 'user'];

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
   * Tests ConfigurableEventHandlerEntityBundle.
   *
   * Test that rules are triggered correctly based upon the fully qualified
   * event name as well as the base event name.
   *
   * @todo Add integrity check that node.field_integer is detected by Rules.
   */
  public function testConfigurableEventHandler() {
    // Create rule1 with the 'rules_entity_presave:node--page' event.
    $rule1 = $this->expressionManager->createRule();
    $rule1->addAction('rules_test_log',
      ContextConfig::create()
        ->map('message', 'node.field_integer.0.value')
    );
    $config_entity1 = $this->storage->create([
      'id' => 'test_rule1',
    ]);
    $config_entity1->set('events', [
      ['event_name' => 'rules_entity_presave:node--page'],
    ]);
    $config_entity1->set('expression', $rule1->getConfiguration());
    $config_entity1->save();

    // Create rule2 with the 'rules_entity_presave:node' event.
    $rule2 = $this->expressionManager->createRule();
    $rule2->addAction('rules_test_log',
      ContextConfig::create()
        ->map('message', 'node.field_integer.1.value')
    );
    $config_entity2 = $this->storage->create([
      'id' => 'test_rule2',
    ]);
    $config_entity2->set('events', [
      ['event_name' => 'rules_entity_presave:node'],
    ]);
    $config_entity2->set('expression', $rule2->getConfiguration());
    $config_entity2->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    // Add node.field_integer.0.value to rules log message, read result.
    $this->node->field_integer->setValue(['0' => 11, '1' => 22]);

    // Trigger node save.
    $entity_type_id = $this->node->getEntityTypeId();
    $event = new EntityEvent($this->node, [
      $entity_type_id => $this->node,
      $entity_type_id . '_unchanged' => $this->node,
    ]);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch("rules_entity_presave:$entity_type_id", $event);

    // Test that the action in the rule1 logged node value.
    $this->assertRulesLogEntryExists(11, 1);
    // Test that the action in the rule2 logged node value.
    $this->assertRulesLogEntryExists(22, 0);
  }

}
