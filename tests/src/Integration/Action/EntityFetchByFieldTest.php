<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntityFetchByFieldTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntityFetchByField
 * @group rules_actions
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

    // Prepare our own dummy entityManager as the entityManager in
    // RulesEntityIntegrationTestBase does not mock the getStorage method.
    $this->entityManager = $this->prophesize(EntityManagerInterface::class);

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->getBundleInfo('test')
      ->willReturn(['entity_test' => ['label' => 'Entity Test']]);

    $this->container->set('entity.manager', $this->entityManager->reveal());

    // The base field definitions for entity_test aren't used, and would
    // require additional mocking.
    $this->entityManager->getBaseFieldDefinitions('test')->willReturn([]);

    $entityType = new ContentEntityType([
      'id' => 'test',
      'label' => 'Test',
      'entity_keys' => [
        'bundle' => 'bundle',
      ],
    ]);
    $this->entityManager->getDefinitions()
      ->willReturn(['test' => $entityType]);

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
    $entityStorage = $this->prophesize(EntityStorageInterface::class);
    $entityStorage->loadByProperties([$field_name => $field_value])
      ->willReturn($entities);
    $this->entityManager->getStorage($entity_type)
      ->willReturn($entityStorage->reveal());

    // Set context values for EntityFetchByField action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('field_name', $field_name)
      ->setContextValue('field_value', $field_value)
      ->execute();

    // Test that executing action without a value for limit returns the dummy entities array.
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
    $entityStorage = $this->prophesize(EntityStorageInterface::class);
    $entityStorage->loadMultiple($entity_ids)
      ->willReturn($entities)
      ->shouldBeCalledTimes(1);
    $entityStorage->getQuery()
      ->willReturn($query)
      ->shouldBeCalledTimes(1);
    $this->entityManager->getStorage($entity_type)
      ->willReturn($entityStorage->reveal())
      ->shouldBeCalledTimes(1);

    // Set context values for EntityFetchByField action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('field_name', $field_name)
      ->setContextValue('field_value', $field_value)
      ->setContextValue('limit', $limit)
      ->execute();

    // Test that executing action with a value for limit returns the dummy entities array.
    $this->assertEquals($entities, $this->action->getProvidedContext('entity_fetched')->getContextValue('entity_fetched'));
  }

  /**
   * Tests that the context provided by the action execution has the correct entity type.
   *
   * @covers ::execute
   */
  function testActionExecutionProvidedContextEntityType() {
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
    $entityStorage = $this->prophesize(EntityStorageInterface::class);
    $entityStorage->loadByProperties([$field_name => $field_value])
      ->willReturn($entities);
    $this->entityManager->getStorage($entity_type)
      ->willReturn($entityStorage->reveal())
      ->shouldBeCalledTimes(1);

    // Set context values for EntityFetchByField action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('field_name', $field_name)
      ->setContextValue('field_value', $field_value)
      ->execute();

    // @todo Test that the provided context has the correct entity type.
  }

  /**
   * @covers ::refineContextDefinitions
   */
  public function testRefiningContextDefinitions() {
    $this->action->setContextValue('type', 'entity_test');
    $this->action->refineContextdefinitions();
    $this->assertEquals(
      $this->action->getProvidedContextDefinition('entity_fetched')
        ->getDataType(), 'entity:entity_test'
    );
  }

}
