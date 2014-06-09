<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityIsOfBundleTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\EntityIsOfBundle;

/**
 * Tests the 'Entity is of Bundle' condition.
 */
class EntityIsOfBundleTest extends ConditionTestBase {

  /**
   * The mocked condition to be tested.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\rules\Plugin\Condition\PathHasAlias
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
      'name' => 'Entity is of bundle condition test',
      'description' => 'Tests whether an entity is of a particular [type and] bundle.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->condition = new EntityIsOfBundle([], '', [], $this->typedDataManager);
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
    $this->assertEquals('The type of the evaluated entity.', $definition->getDescription());

    // Test that the 'bundle' context is properly defined.
    $language = $this->condition->getContext('bundle');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $language);
    $definition = $language->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Bundle', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertEquals('The bundle of the evaluated entity.', $definition->getDescription());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Entity is of bundle', $this->condition->summary());
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

    $this->assertSame($this->condition, $this->condition->setContextValue('bundle', 'my-bundle'));
    $this->assertSame('my-bundle', $this->condition->getContextValue('bundle'));

    $this->assertSame($this->condition, $this->condition->setContextValue('type', 'my-entity-type'));
    $this->assertSame('my-entity-type', $this->condition->getContextValue('type'));
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->exactly(3))
      ->method('getEntityTypeId')
      ->will($this->returnValue('node'));

    $entity->expects($this->exactly(3))
      ->method('bundle')
      ->will($this->returnValue('page'));

    // Add the test node to our context as the evaluated entity, along with
    // explicit entity type and bundle strings.
    // First, test with values that should evaluate TRUE.
    $this->condition->setContextValue('entity', $entity)
      ->setContextValue('type', 'node')
      ->setContextValue('bundle', 'page');

    $this->assertTrue($this->condition->evaluate());

    // Then test with values that should evaluate FALSE.
    $this->condition->setContextValue('bundle', 'article');
    $this->assertFalse($this->condition->evaluate());

    $this->condition->setContextValue('type', 'taxonomy_term')
      ->setContextValue('bundle', 'page');
    $this->assertFalse($this->condition->evaluate());
  }
}
