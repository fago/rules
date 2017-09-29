<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntityFetchByField
 * @group RulesAction
 */
class EntityFetchByFieldTest extends RulesEntityIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->action = $this->actionManager->createInstance('rules_entity_fetch_by_field');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Fetch entities by field', $this->action->summary());
  }

  /**
   * Tests action execution when no value for limit is provided.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithNoLimit() {
    // Create variables for action context values.
    $entity_type = 'entity_test';
    $field_name = 'test_field';
    $field_value = 'llama';

    // Create an array of dummy entities.
    $entities = [];
    for ($i = 0; $i < 2; $i++) {
      $entity = $this->prophesize(EntityInterface::class);
      $entities[] = $entity->reveal();
    }

    // Create dummy entity storage object.
    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $entity_storage->loadByProperties([$field_name => $field_value])
      ->willReturn($entities);
    $this->entityTypeManager->getStorage($entity_type)
      ->willReturn($entity_storage->reveal());

    // Set context values for EntityFetchByField action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('field_name', $field_name)
      ->setContextValue('field_value', $field_value)
      ->execute();

    // Test that executing action without a value for limit returns the dummy
    // entities array.
    $this->assertEquals($entities, $this->action->getProvidedContext('entity_fetched')->getContextValue('entity_fetched'));
  }

  /**
   * Tests action execution when a value for limit is provided.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithLimit() {
    $entity_type = 'entity_test';
    $field_name = 'test_field';
    $field_value = 'llama';
    $limit = 2;

    // Create an array of dummy entities.
    $entities = array_map(function () {
      return $this->prophesize(EntityInterface::class)->reveal();
    }, range(1, $limit));

    // Creates entity ids for new dummy array of entities.
    $entity_ids = range(1, $limit);

    // Create dummy query object.
    $query = $this->prophesize(QueryInterface::class);
    $query->condition($field_name, $field_value, '=')
      ->willReturn($query->reveal())
      ->shouldBeCalledTimes(1);
    $query->range(0, $limit)
      ->willReturn($query->reveal())
      ->shouldBeCalledTimes(1);
    $query->execute()
      ->willReturn($entity_ids)
      ->shouldBeCalledTimes(1);

    // Create dummy entity storage object.
    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $entity_storage->loadMultiple($entity_ids)
      ->willReturn($entities)
      ->shouldBeCalledTimes(1);
    $entity_storage->getQuery()
      ->willReturn($query)
      ->shouldBeCalledTimes(1);
    $this->entityTypeManager->getStorage($entity_type)
      ->willReturn($entity_storage->reveal())
      ->shouldBeCalledTimes(1);

    // Set context values for EntityFetchByField action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('field_name', $field_name)
      ->setContextValue('field_value', $field_value)
      ->setContextValue('limit', $limit)
      ->execute();

    // Test that executing action with a value for limit returns the dummy
    // entities array.
    $this->assertEquals($entities, $this->action->getProvidedContext('entity_fetched')->getContextValue('entity_fetched'));
  }

  /**
   * Tests that the action execution loads the entity from storage.
   *
   * @covers ::execute
   */
  public function testActionExecutionProvidedContextEntityType() {
    // Create variables for action context values.
    $entity_type = 'entity_test';
    $field_name = 'test_field';
    $field_value = 'llama';

    // Create an array of dummy entities.
    $entities = [];
    for ($i = 0; $i < 2; $i++) {
      $entity = $this->prophesize(EntityInterface::class)->reveal();
      $entities[] = $entity;
    }

    // Create dummy entity storage object.
    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $entity_storage->loadByProperties([$field_name => $field_value])
      ->willReturn($entities);
    $this->entityTypeManager->getStorage($entity_type)
      ->willReturn($entity_storage->reveal())
      ->shouldBeCalledTimes(1);

    // Set context values for EntityFetchByField action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('field_name', $field_name)
      ->setContextValue('field_value', $field_value)
      ->execute();
  }

  /**
   * @covers ::refineContextDefinitions
   */
  public function testRefiningContextDefinitions() {
    $this->action->setContextValue('type', 'entity_test');
    $this->action->refineContextDefinitions([]);
    $this->assertEquals(
      $this->action->getProvidedContextDefinition('entity_fetched')
        ->getDataType(), 'entity:entity_test'
    );
  }

}
