<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityIsOfBundleTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\EntityIsOfType;

/**
 * Tests the 'Entity is of type' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityIsOfType
 *
 * @see \Drupal\rules\Plugin\Condition\EntityIsOfType
 */
class EntityIsOfTypeTest extends ConditionTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * The mocked typed data manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Entity is of type condition test',
      'description' => 'Tests that an entity is of a particular type.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->condition = new EntityIsOfType([], '', [], $this->typedDataManager);
    $this->condition->setStringTranslation($this->getMockStringTranslation());
  }

  /**
   * Tests that the dependencies are properly set in the constructor.
   *
   * @covers ::__construct()
   */
  public function testConstructor() {
    $this->assertSame($this->typedDataManager, $this->condition->getTypedDataManager());
  }

  /**
   * Tests the context definitions.
   *
   * @covers ::contextDefinitions()
   */
  public function testContextDefinition() {
    // Test that the 'entity' context is properly defined.
    $context = $this->condition->getContext('entity');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Entity', $definition->getLabel());
    $this->assertEquals('entity', $definition->getDataType());
    $this->assertEquals('Specifies the entity for which to evaluate the condition.', $definition->getDescription());
    $this->assertTrue($definition->isRequired());

    // Test that the 'type' context is properly defined.
    $language = $this->condition->getContext('type');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $language);
    $definition = $language->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Type', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertEquals('The entity type specified by the condition.', $definition->getDescription());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Entity is of type', $this->condition->summary());
  }

  /**
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValues() {
    // Test setting and getting context values.
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $this->assertSame($this->condition, $this->condition->setContextValue('entity', $entity));
    $this->assertSame($entity, $this->condition->getContextValue('entity'));

    $this->assertSame($this->condition, $this->condition->setContextValue('type', 'my-entity-type'));
    $this->assertSame('my-entity-type', $this->condition->getContextValue('type'));
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->exactly(2))
      ->method('getEntityTypeId')
      ->will($this->returnValue('node'));

    // Add the test node to our context as the evaluated entity, along with an
    // explicit entity type string.
    // First, test with a value that should evaluate TRUE.
    $this->condition->setContextValue('entity', $entity)
      ->setContextValue('type', 'node');
    $this->assertTrue($this->condition->evaluate());

    // Then test with values that should evaluate FALSE.
    $this->condition->setContextValue('type', 'taxonomy_term');
    $this->assertFalse($this->condition->evaluate());
  }
}
