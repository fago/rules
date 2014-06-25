<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsOfTypeTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\NodeIsOfType;

/**
 * Tests the 'Node is sticky' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsOfType
 *
 * @see \Drupal\rules\Plugin\Condition\NodeIsOfType
 */
class NodeIsOfTypeTest extends ConditionTestBase {

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
      'name' => 'Node is of type condition test',
      'description' => 'Tests the condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->condition = new NodeIsOfType([], '', [], $this->typedDataManager);
    $this->condition->setStringTranslation($this->getMockStringTranslation());
  }

  /**
   * Tests the context definitions.
   *
   * @covers ::contextDefinitions()
   */
  public function testContextDefinition() {
    // Test that the 'node' context is properly defined.
    $context = $this->condition->getContext('node');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Node', $definition->getLabel());
    $this->assertEquals('entity:node', $definition->getDataType());
    $this->assertTrue($definition->isRequired());

    // Test that the 'types' context is properly defined.
    $context = $this->condition->getContext('types');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Content types', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertEquals('Check for the the allowed node types.', $definition->getDescription());
    $this->assertTrue($definition->isMultiple());
    $this->assertTrue($definition->isRequired());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Node is of type', $this->condition->summary());
  }

  /**
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValue() {
    $node = $this->getMock('Drupal\node\NodeInterface');
    $types = ['page', 'article'];

    // Test setting and getting the context value.
    $this->assertSame($this->condition, $this->condition->setContextValue('node', $node));
    $this->assertSame($node, $this->condition->getContextValue('node'));

    // Test setting and getting the context value.
    $this->assertSame($this->condition, $this->condition->setContextValue('types', $types));
    $this->assertSame($types, $this->condition->getContextValue('types'));
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $node = $this->getMock('Drupal\node\NodeInterface');
    $node->expects($this->any())
      ->method('getType')
      ->will($this->returnValue('page'));

    // Set the node context value.
    $this->condition->setContextValue('node', $node);

    // Test evaluation with a list that contains the actual node type.
    $this->condition->setContextValue('types', ['page', 'article']);
    $this->assertTrue($this->condition->evaluate());

    // Test with a list that does not contain the actual node type.
    $this->condition->setContextValue('types', ['apple', 'banana']);
    $this->assertFalse($this->condition->evaluate());
  }

}

