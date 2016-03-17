<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Kernel\Engine\AutocompleteTest.
 */

namespace Drupal\Tests\rules\Kernel\Engine;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Kernel\RulesDrupalTestBase;

/**
 * Tests that data selector autocomplete results work correctly.
 *
 * @group rules
 */
class AutocompleteTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['field', 'rules', 'node', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');

    $entity_type_manager = $this->container->get('entity_type.manager');
    $entity_type_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    // Create a multi-value integer field for testing.
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
  }

  /**
   * Tests autocompletion works for a variable in the metadata state.
   */
  public function testAutocomplete() {
    $rule = $this->expressionManager->createRule();
    $action = $this->expressionManager->createAction('rules_data_set');
    $rule->addExpressionObject($action);

    $results = RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->autocomplete('e', $action);

    $this->assertSame(['entity'], $results);
  }

  /**
   * Test various node example data selectors.
   */
  public function testNodeAutocomplete() {
    $rule = $this->expressionManager->createRule();
    $rule->addAction('rules_data_set');

    $component = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node:page'));

    // Tests that "node.uid.en" returns the suggestion "node.uid.entity".
    $results = $component->autocomplete('node.uid.en');
    $this->assertSame(['node.uid.entity'], $results);

    // Tests that "node." returns all available fields on a node.
    $results = $component->autocomplete('node.');
    $expected = [
      'node.changed',
      'node.created',
      'node.default_langcode',
      'node.field_integer',
      'node.langcode',
      'node.nid',
      'node.promote',
      'node.revision_log',
      'node.revision_timestamp',
      'node.revision_translation_affected',
      'node.revision_uid',
      'node.status',
      'node.sticky',
      'node.title',
      'node.type',
      'node.uid',
      'node.uuid',
      'node.vid',
    ];
    $this->assertSame($expected, $results);

    // Tests that "node.uid.entity.na" returns "node.uid.entity.name".
    $results = $component->autocomplete('node.uid.entity.na');
    $this->assertSame(['node.uid.entity.name'], $results);

    // A multi-valued field should show numeric indices suggestions.
    $results = $component->autocomplete('node.field_integer.');
    $this->assertSame([
      'node.field_integer.0',
      'node.field_integer.1',
      'node.field_integer.2',
      'node.field_integer.value',
    ], $results);

    // A single-valued field should not show numeric indices suggestions.
    $results = $component->autocomplete('node.title.');
    $this->assertSame([
      'node.title.value',
    ], $results);
  }

  /**
   * Tests that autocomplete results for a flat list are correct.
   */
  public function testListAutocomplete() {
    $rule = $this->expressionManager->createRule();
    $rule->addAction('rules_data_set');

    $context_definition = ContextDefinition::create('integer');
    $context_definition->setMultiple();
    $component = RulesComponent::create($rule)
      ->addContextDefinition('list', $context_definition);

    $results = $component->autocomplete('list.');
    $this->assertSame([
      'list.0',
      'list.1',
      'list.2',
    ], $results);
  }

}
