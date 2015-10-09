<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Integration\Action\EntityFetchByIdTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntityFetchById
 * @group rules_actions
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
    // Prepare entity storage to return dummy entity on the 'load' execution.
    $entity = $this->prophesize(EntityInterface::class);
    $entityStorage = $this->prophesize(EntityStorageInterface::class);
    $entityStorage->load(1)->willReturn($entity->reveal())
      ->shouldBeCalledTimes(1);
    $this->entityManager->getStorage('test')
      ->willReturn($entityStorage->reveal())
      ->shouldBeCalledTimes(1);

    $this->action
      ->setContextValue('entity_type_id', 'test')
      ->setContextValue('entity_id', 1)
      ->execute();

    // Entity load with type 'test' and id '1' should return the dummy entity.
    $this->assertEquals($entity->reveal(), $this->action->getProvidedContext('entity')->getContextValue('entity'), 'Action returns the loaded entity for fetching entity by id.');
  }

}
