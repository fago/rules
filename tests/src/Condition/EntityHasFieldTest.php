<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityHasFieldTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\EntityHasField;

/**
 * Tests the 'Entity has field' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\ EntityHasField
 *
 * @see \Drupal\rules\Plugin\Condition\ EntityHasField
 */
class EntityHasFieldTest extends ConditionTestBase {

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

    $this->condition = new EntityHasField([], '', [], $this->typedDataManager);
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

    // Test that the 'field' context is properly defined.
    $context = $this->condition->getContext('field');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Field', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertEquals('The name of the field to check for.', $definition->getDescription());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Entity has field', $this->condition->summary());
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

    $this->assertSame($this->condition, $this->condition->setContextValue('field', 'my-field'));
    $this->assertSame('my-field', $this->condition->getContextValue('field'));
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    // We can't mock the ContentEntityInterface because there is a bug in PHPUnit below
    // version 3.8 that causes mocking of interfaces that extend \Traversable
    // to fail. @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
    $entity = $this->getMocKBuilder('Drupal\Core\Entity\ContentEntityBase')
      ->disableOriginalConstructor()
      ->getMock();

    $entity->expects($this->exactly(2))
      ->method('hasField')
      ->will($this->returnValueMap([
        ['existing-field', TRUE],
        ['non-existing-field', FALSE],
      ]));

    $this->condition->setContextValue('entity', $entity);

    // Test with an existing field.
    $this->condition->setContextValue('field', 'existing-field');
    $this->assertTrue($this->condition->evaluate());

    // Test with a non-existing field.
    $this->condition->setContextValue('field', 'non-existing-field');
    $this->assertFalse($this->condition->evaluate());
  }
}
