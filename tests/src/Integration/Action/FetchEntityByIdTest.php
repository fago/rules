<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Integration\Action\FetchEntityByIdTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\FetchEntityById
 * @group rules_actions
 */
class FetchEntityByIdTest extends RulesEntityIntegrationTestBase {

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
    $this->entityManager = $this->getMockBuilder('Drupal\Core\Entity\EntityManager')
      ->setMethods(['getBundleInfo', 'getBaseFieldDefinitions', 'getStorage'])
      ->setConstructorArgs([
        $this->namespaces,
        $this->moduleHandler,
        $this->cacheBackend,
        $this->getMock('Drupal\Core\Language\LanguageManagerInterface'),
        $this->getStringTranslationStub(),
        $this->getClassResolverStub(),
        $this->typedDataManager,
        $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface'),
        $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
      ])
      ->getMock();

    // The base field definitions for entity_test aren't used, and would
    // require additional mocking.
    $this->entityManager->expects($this->any())
      ->method('getBaseFieldDefinitions')
      ->willReturn([]);

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->expects($this->any())
      ->method('getBundleInfo')
      ->with($this->anything())
      ->willReturn(['test' => ['label' => 'Test']]);
    $this->container->set('entity.manager', $this->entityManager);

    $this->action = $this->actionManager->createInstance('rules_fetch_entity_by_id');
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
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
      ->method('load')
      ->with(1)
      ->will($this->returnValue($entity));
    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with('test')
      ->will($this->returnValue($entityStorage));

    $this->action
      ->setContextValue('entity_type', 'test')
      ->setContextValue('entity_id', 1)
      ->execute();

    // Entity load with type 'test' and id '1' should return the dummy entity.
    $this->assertEquals($entity, $this->action->getProvidedContext('entity')->getContextValue('entity'), 'Action returns the loaded entity for fetching entity by id.');
  }
}
