<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityHasFieldTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\system\Tests\Entity\EntityUnitTestBase;
use Drupal\entity_test\Entity;

/**
 * Tests the 'Entity has field' condition.
 */
class EntityHasFieldTest extends EntityUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rules'];

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * A field to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $fieldStorage;

  /**
   * The instance used in this test class.
   *
   * @var \Drupal\field\Entity\FieldInstanceConfig
   */
  protected $fieldInstanceStorage;

  /**
   * The entity_test storage to create the test entities.
   *
   * @var \Drupal\entity_test\EntityTestStorage
   */
  protected $entityStorage;

  /**
   * The entity to check for a field in testing.
   *
   * @var \Drupal\Core\Entity\ContentEntityBase
   */
  protected $entity;

  /**
   * The machine name of the field type to create on the entity.
   *
   * @var string
   */
  protected $fieldType = 'text';

  /**
   * The name of the field to create for testing.
   *
   * @var string
   */
  protected $fieldName = 'field_test';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Entity has field condition test',
      'description' => 'Tests the condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();
    $this->conditionManager = $this->container->get('plugin.manager.condition', $this->container->get('container.namespaces'));
    $this->fieldInstanceStorage = $this->entityManager->getStorage('field_instance_config');
    $this->fieldStorage = $this->entityManager->getStorage('field_config');
    $this->entityStorage = $this->entityManager->getStorage('entity_test');

    // Set up test values.
    $this->createTestField();

    $this->entity = $this->entityStorage->create(array('title' => 'test_entity'));
    $this->entity->{$this->fieldName}->value = 'test_value';
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    $condition = $this->conditionManager->createInstance('rules_entity_has_field')
      ->setContextValue('entity', $this->entity)
      ->setContextValue('field', $this->fieldName);
    $this->assertTrue($condition->execute());
  }

  /**
   * Creates the field for testing.
   */
  protected function createTestField() {
    $this->fieldStorage->create(array(
      'name' => $this->fieldName,
      'entity_type' => 'entity_test',
      'type' => $this->fieldType,
    ))->save();
    $this->fieldInstanceStorage->create(array(
      'entity_type' => 'entity_test',
      'field_name' => $this->fieldName,
      'bundle' => 'entity_test',
    ))->save();
  }

}
