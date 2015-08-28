<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Integration\Action\EntityFetchByIdTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
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

    // Prepare dummy entity manager.
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
