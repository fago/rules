<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntityFetchById
 * @group RulesAction
 */
class EntityFetchByIdTest extends RulesEntityIntegrationTestBase {

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

    $this->action = $this->actionManager->createInstance('rules_entity_fetch_by_id');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Fetch entity by id', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {
    $entity_type = 'entity_test';

    // Prepare entity storage to return dummy entity on the 'load' execution.
    $entity = $this->prophesize(EntityInterface::class);
    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $entity_storage->load(1)->willReturn($entity->reveal())
      ->shouldBeCalledTimes(1);
    $this->entityTypeManager->getStorage($entity_type)
      ->willReturn($entity_storage->reveal())
      ->shouldBeCalledTimes(1);

    // Set context values for EntityFetchByField action and execute.
    $this->action
      ->setContextValue('type', $entity_type)
      ->setContextValue('entity_id', 1)
      ->execute();
    // Test that entity load with type 'test' and id '1' should return the
    // dummy entity.
    $this->assertEquals($entity->reveal(), $this->action->getProvidedContext('entity_fetched')->getContextValue('entity_fetched'), 'Action returns the loaded entity for fetching entity by id.');
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
