<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntityCreateTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Entity\EntityStorageBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntityCreate
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
    // EntityCreateDeriver adds required contexts for required fields, and
    // assumes that the bundle field is required.
    $bundleFieldDefinition = $this->prophesize(BaseFieldDefinition::class);

    // The next methods are mocked because EntityCreateDeriver executes them,
    // and the mocked field definition is instantiated without the necessary
    // information.
    $bundleFieldDefinition->getCardinality()->willReturn(1)
      ->shouldBeCalledTimes(1);

    $bundleFieldDefinition->getType()->willReturn('string')
      ->shouldBeCalledTimes(1);

    $bundleFieldDefinition->getLabel()->willReturn('Bundle')
      ->shouldBeCalledTimes(1);

    $bundleFieldDefinition->getDescription()
      ->willReturn('Bundle mock description')
      ->shouldBeCalledTimes(1);

    // Prepare mocked entity storage.
    $entityTypeStorage = $this->prophesize(EntityStorageBase::class);
    $entityTypeStorage->create(['bundle' => 'test'])
      ->willReturn(self::ENTITY_REPLACEMENT);

    // Return the mocked storage controller.
    $this->entityManager->getStorage('test')
      ->willReturn($entityTypeStorage->reveal());

    // Return a mocked list of base fields definitions.
    $this->entityManager->getBaseFieldDefinitions('test')
      ->willReturn(['bundle' => $bundleFieldDefinition->reveal()]);

    // Instantiate the action we are testing.
    $this->action = $this->actionManager->createInstance('rules_entity_create:entity:test');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Create a new test', $this->action->summary());
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
