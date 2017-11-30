<?php

namespace Drupal\Tests\rules\Kernel\Engine;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Kernel\RulesDrupalTestBase;

/**
 * Tests asserting metadata works correctly.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class MetadataAssertionTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'rules',
    'typed_data',
    'system',
    'node',
    'field',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['field']);

    $entity_type_manager = $this->container->get('entity_type.manager');
    $entity_type_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    FieldStorageConfig::create([
      'field_name' => 'field_text',
      'type' => 'string',
      'entity_type' => 'node',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_text',
      'entity_type' => 'node',
      'bundle' => 'page',
    ])->save();
  }

  /**
   * Tests asserting metadata using the EntityIfOfBundle condition.
   */
  public function testAssertingEntityBundle() {
    // When trying to use the field_text field without knowledge of the bundle,
    // the field is not available.
    $rule = $this->expressionManager->createRule();
    $rule->addAction('rules_system_message', ContextConfig::create()
      ->map('message', 'node.field_text.value')
      ->setValue('type', 'status')
    );
    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
    $this->assertEquals(
      'Data selector %selector for context %context_name is invalid. @message',
      $violation_list->get(0)->getMessage()->getUntranslatedString()
    );

    // Now add the EntityIsOfBundle condition and try again.
    $rule->addCondition('rules_entity_is_of_bundle', ContextConfig::create()
      ->map('entity', 'node')
      ->setValue('type', 'node')
      ->setValue('bundle', 'page')
    );
    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node'))
      ->checkIntegrity();
    $this->assertEquals(0, iterator_count($violation_list));
  }

  /**
   * Tests asserted metadata is handled correctly in OR and AND containers.
   */
  public function testAssertingWithLogicalOperations() {
    // Add an nested AND and make sure it keeps working.
    $rule = $this->expressionManager->createRule();
    $and = $this->expressionManager->createAnd();
    $and->addCondition('rules_entity_is_of_bundle', ContextConfig::create()
      ->map('entity', 'node')
      ->setValue('type', 'node')
      ->setValue('bundle', 'page')
    );
    $rule->addExpressionObject($and);
    $rule->addAction('rules_system_message', ContextConfig::create()
      ->map('message', 'node.field_text.value')
      ->setValue('type', 'status')
    );
    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node'))
      ->checkIntegrity();
    $this->assertEquals(0, iterator_count($violation_list));

    // Add an nested OR and make sure it is ignored.
    $rule = $this->expressionManager->createRule();
    $or = $this->expressionManager->createOr();
    $or->addCondition('rules_entity_is_of_bundle', ContextConfig::create()
      ->map('entity', 'node')
      ->setValue('type', 'node')
      ->setValue('bundle', 'page')
    );
    $rule->addExpressionObject($or);
    $rule->addAction('rules_system_message', ContextConfig::create()
      ->map('message', 'node.field_text.value')
      ->setValue('type', 'status')
    );
    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
  }

  /**
   * Tests asserted metadata of negated conditions is ignored.
   */
  public function testAssertingOfNegatedConditions() {
    // Negate the condition only and make sure it is ignored.
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_entity_is_of_bundle', ContextConfig::create()
      ->map('entity', 'node')
      ->setValue('type', 'node')
      ->setValue('bundle', 'page')
    )->negate(TRUE);
    $rule->addAction('rules_system_message', ContextConfig::create()
      ->map('message', 'node.field_text.value')
      ->setValue('type', 'status')
    );
    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));

    // Add an negated AND and make sure it is ignored.
    $rule = $this->expressionManager->createRule();
    $and = $this->expressionManager->createAnd();
    $and->addCondition('rules_entity_is_of_bundle', ContextConfig::create()
      ->map('entity', 'node')
      ->setValue('type', 'node')
      ->setValue('bundle', 'page')
    );
    $and->negate(TRUE);
    $rule->addExpressionObject($and);
    $rule->addAction('rules_system_message', ContextConfig::create()
      ->map('message', 'node.field_text.value')
      ->setValue('type', 'status')
    );
    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('node', ContextDefinition::create('entity:node'))
      ->checkIntegrity();
    $this->assertEquals(1, iterator_count($violation_list));
  }

}
