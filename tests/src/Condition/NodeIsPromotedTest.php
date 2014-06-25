<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsPromotedTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\NodeIsPromoted;

/**
 * Tests the 'Node is promoted' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsPromoted
 *
 * @see \Drupal\rules\Plugin\Condition\NodeIsPromoted
 */
class NodeIsPromotedTest extends ConditionTestBase {

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
      'name' => 'Node is promoted condition tests',
      'description' => 'Tests the node is promoted condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->condition = new NodeIsPromoted([], '', [], $this->typedDataManager);
    $this->condition->setStringTranslation($this->getMockStringTranslation());
  }

  /**
   * Tests the context definitions.
   *
   * @covers ::contextDefinitions()
   */
  public function testContextDefinition() {
    // Test that the 'path' context is properly defined.
    $context = $this->condition->getContext('node');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Node', $definition->getLabel());
    $this->assertEquals('entity:node', $definition->getDataType());
    $this->assertTrue($definition->isRequired());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Node is promoted', $this->condition->summary());
  }

  /**
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValue() {
    $node = $this->getMock('Drupal\node\NodeInterface');

    // Test setting and getting the context value.
    $this->assertSame($this->condition, $this->condition->setContextValue('node', $node));
    $this->assertSame($node, $this->condition->getContextValue('node'));
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $node = $this->getMock('Drupal\node\NodeInterface');
    $node->expects($this->at(0))
      ->method('isPromoted')
      ->will($this->returnValue(TRUE));

    $node->expects($this->at(1))
      ->method('isPromoted')
      ->will($this->returnValue(FALSE));

    // Set the node context value.
    $this->condition->setContextValue('node', $node);

    // Test evaluation. The first invocation should return TRUE, the second
    // should return FALSE.
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());
  }

}
