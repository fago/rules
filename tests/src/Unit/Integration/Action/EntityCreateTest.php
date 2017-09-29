<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Entity\EntityStorageBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\rules\Context\ContextDefinition;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntityCreate
 * @group RulesAction
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

    // Prepare some mocked bundle field definitions. This is needed because
    // EntityCreateDeriver adds required contexts for required fields, and
    // assumes that the bundle field is required.
    $bundle_field_definition = $this->prophesize(BaseFieldDefinition::class);
    $bundle_field_definition_optional = $this->prophesize(BaseFieldDefinition::class);
    $bundle_field_definition_required = $this->prophesize(BaseFieldDefinition::class);

    $property_definition = $this->prophesize(DataDefinitionInterface::class);
    $property_definition->getDataType()->willReturn('string');

    $item_definition = $this->prophesize(FieldItemDataDefinition::class);
    $item_definition->getPropertyDefinition(Argument::any())
      ->willReturn($property_definition->reveal());
    $item_definition->getMainPropertyName()->willReturn('value');

    // The next methods are mocked because EntityCreateDeriver executes them,
    // and the mocked field definition is instantiated without the necessary
    // information.
    $bundle_field_definition->getItemDefinition()
      ->willReturn($item_definition->reveal());
    $bundle_field_definition->getCardinality()->willReturn(1)
      ->shouldBeCalledTimes(1);
    $bundle_field_definition->getType()->willReturn('string');
    $bundle_field_definition->getLabel()->willReturn('Bundle')
      ->shouldBeCalledTimes(1);
    $bundle_field_definition->getDescription()
      ->willReturn('Bundle mock description')
      ->shouldBeCalledTimes(1);

    $bundle_field_definition_required->getItemDefinition()
      ->willReturn($item_definition->reveal());
    $bundle_field_definition_required->getCardinality()->willReturn(1)
      ->shouldBeCalledTimes(1);
    $bundle_field_definition_required->getType()->willReturn('string');
    $bundle_field_definition_required->getLabel()->willReturn('Required field')
      ->shouldBeCalledTimes(1);
    $bundle_field_definition_required->getDescription()
      ->willReturn('Required field mock description')
      ->shouldBeCalledTimes(1);
    $bundle_field_definition_required->isRequired()
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(1);

    $bundle_field_definition_optional->isRequired()
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(1);

    // Prepare mocked entity storage.
    $entity_type_storage = $this->prophesize(EntityStorageBase::class);
    $entity_type_storage->create(['bundle' => 'test', 'field_required' => NULL])
      ->willReturn(self::ENTITY_REPLACEMENT);

    // Return the mocked storage controller.
    $this->entityTypeManager->getStorage('test')
      ->willReturn($entity_type_storage->reveal());

    // Return a mocked list of base fields definitions.
    $this->entityFieldManager->getBaseFieldDefinitions('test')
      ->willReturn([
        'bundle' => $bundle_field_definition->reveal(),
        'field_required' => $bundle_field_definition_required->reveal(),
        'field_optional' => $bundle_field_definition_optional->reveal(),
      ]);

    // Instantiate the action we are testing.
    $this->action = $this->actionManager->createInstance('rules_entity_create:test');
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

  /**
   * Tests context definitions for the bundle and required fields.
   *
   * @covers \Drupal\rules\Plugin\RulesAction\EntityCreateDeriver::getDerivativeDefinitions
   */
  public function testRequiredContexts() {
    $context_definitions = $this->action->getContextDefinitions();
    $this->assertCount(2, $context_definitions);

    $this->assertArrayHasKey('bundle', $context_definitions);
    $this->assertEquals(ContextDefinition::ASSIGNMENT_RESTRICTION_INPUT, $context_definitions['bundle']->getAssignmentRestriction());
    $this->assertTrue($context_definitions['bundle']->isRequired());

    $this->assertArrayHasKey('field_required', $context_definitions);
    $this->assertNull($context_definitions['field_required']->getAssignmentRestriction());
    $this->assertFalse($context_definitions['field_required']->isRequired());
  }

  /**
   * Tests the context refining.
   *
   * @covers ::refineContextDefinitions
   */
  public function testRefiningContextDefinitions() {
    $this->action->setContextValue('bundle', 'bundle_test');
    $this->action->refineContextDefinitions([]);
    $this->assertEquals(
      $this->action->getProvidedContextDefinition('entity')
        ->getDataType(), 'entity:test:bundle_test'
    );
  }

}
