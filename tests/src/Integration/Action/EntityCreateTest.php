<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntityCreateTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\EntityCreate
 * @group rules_actions
 */
class EntityCreateTest extends RulesEntityIntegrationTestBase {

  /**
   * A constant that will be used instead of an entity.
   */
  const ENTITY_REPLACEMENT = 'This is a fake entity';

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

    // Prepare mocked bundle field definition. This is needed because
    // EntityCreateDeriver adds required contexs for required fields, and
    // assumes that the bundle field is required.
    $this->bundleFieldDefinition = $this->getMockBuilder('Drupal\Core\Field\BaseFieldDefinition')
      ->disableOriginalConstructor()
      ->getMock();

    // The next methods are mocked because EntityCreateDeriver executes them,
    // and the mocked field definition is instantiated without the necessary
    // information.
    $this->bundleFieldDefinition
      ->expects($this->once())
      ->method('getCardinality')
      ->willReturn(1);

    $this->bundleFieldDefinition
      ->expects($this->once())
      ->method('getType')
      ->willReturn('string');

    $this->bundleFieldDefinition
      ->expects($this->once())
      ->method('getLabel')
      ->willReturn('Bundle');

    $this->bundleFieldDefinition
      ->expects($this->once())
      ->method('getDescription')
      ->willReturn('Bundle mock description');

    // Prepare an content entity type instance.
    $this->entityType = new ContentEntityType([
      'id' => 'test',
      'label' => 'Test',
      'entity_keys' => [
        'bundle' => 'bundle',
      ],
    ]);

    // Prepare mocked entity storage.
    $this->entityTypeStorage = $this->getMockBuilder('Drupal\Core\Entity\EntityStorageBase')
      ->setMethods(['create'])
      ->setConstructorArgs([$this->entityType])
      ->getMockForAbstractClass();

    $this->entityTypeStorage
      ->expects($this->any())
      ->method('create')
      ->willReturn(self::ENTITY_REPLACEMENT);

    // Prepare mocked entity manager.
    $this->entityManager = $this->getMockBuilder('Drupal\Core\Entity\EntityManager')
      ->setMethods(['getBundleInfo', 'getStorage', 'getDefinitions', 'getBaseFieldDefinitions'])
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

    // Return the mocked storage controller.
    $this->entityManager
      ->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->entityTypeStorage);

    // Return a mocked list of base fields defintions.
    $this->entityManager
      ->expects($this->any())
      ->method('getBaseFieldDefinitions')
      ->willReturn(['bundle' => $this->bundleFieldDefinition]);

    // Return a mocked list of entity types.
    $this->entityManager
      ->expects($this->any())
      ->method('getDefinitions')
      ->willReturn(['test' => $this->entityType]);

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager
      ->expects($this->any())
      ->method('getBundleInfo')
      ->with($this->anything())
      ->willReturn(['test' => ['label' => 'Test']]);

    $this->container->set('entity.manager', $this->entityManager);

    // Instantiate the action we are testing.
    $this->action = $this->actionManager->createInstance('rules_entity_create:entity:test');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Create a new test entity', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {
    $this->action->setContextValue('bundle', 'test');
    $this->action->execute();
    $entity = $this->action->getProvidedContext('entity')->getContextValue();
    $this->assertEquals(self::ENTITY_REPLACEMENT, $entity);
  }

}
