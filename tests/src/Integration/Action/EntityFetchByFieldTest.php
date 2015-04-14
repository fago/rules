<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntityFetchByFieldTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\EntityFetchByField
 * @group rules_action
 */
class EntityFetchByFieldTest extends RulesEntityIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Prepare our own dummy entityManager as the entityManager in
    // RulesEntityIntegrationTestBase does not mock the getStorage method.
    $this->entityManager = $this->getMockBuilder('Drupal\Core\Entity\EntityManager')
      ->setMethods(['getBundleInfo', 'getStorage', 'getBaseFieldDefinitions'])
      ->setConstructorArgs([
        $this->namespaces,
        $this->moduleHandler,
        $this->cacheBackend,
        $this->languageManager,
        $this->getStringTranslationStub(),
        $this->getClassResolverStub(),
        $this->typedDataManager,
        $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface'),
        $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
      ])
      ->getMock();

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->expects($this->any())
      ->method('getBundleInfo')
      ->with($this->anything())
      ->willReturn(['entity_test' => ['label' => 'Entity Test']]);
    $this->container->set('entity.manager', $this->entityManager);

    // The base field definitions for entity_test aren't used, and would
    // require additional mocking.
    $this->entityManager->expects($this->any())
      ->method('getBaseFieldDefinitions')
      ->willReturn([]);

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
      $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
      $entities[] = $entity;
    }

    // Create dummy entity storage object.
    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
      ->method('loadByProperties')
      ->with([$field_name => $field_value])
      ->willReturn($entities);
    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with($entity_type)
      ->willReturn($entityStorage);

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
      return $this->getMock('Drupal\Core\Entity\EntityInterface');
    }, range(1, $limit));

    // Creates entity ids for new dummy array of entities.
    $entity_ids = range(1, $limit);

    // Create dummy query object.
    $query = $this->getMock('Drupal\Core\Entity\Query\QueryInterface');
    $query->expects($this->once())
      ->method('condition')
      ->with($field_name, $field_value, '=')
      ->willReturn($query);
    $query->expects($this->once())
      ->method('range')
      ->with(0, $limit)
      ->willReturn($query);
    $query->expects($this->once())
      ->method('execute')
      ->willReturn($entity_ids);

    // Create dummy entity storage object.
    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
      ->method('loadMultiple')
      ->with($entity_ids)
      ->willReturn($entities);
    $entityStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);
    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with($entity_type)
      ->willReturn($entityStorage);

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
      $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
      $entities[] = $entity;
    }

    // Create dummy entity storage object.
    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
      ->method('loadByProperties')
      ->with([$field_name => $field_value])
      ->willReturn($entities);
    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with($entity_type)
      ->willReturn($entityStorage);

    // Set context values for EntityFetchByField action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('field_name', $field_name)
      ->setContextValue('field_value', $field_value)
      ->execute();

    // @todo Test that the provided context has the correct entity type.
  }

}
