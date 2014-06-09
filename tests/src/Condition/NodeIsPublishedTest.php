<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsPublishedTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\NodeIsPublished;

/**
 * Tests the 'Node is published' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsPublished
 *
 * @see \Drupal\rules\Plugin\Condition\NodeIsPublished
 */
class NodeIsPublishedTest extends ConditionTestBase {

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
      'name' => 'Node is published condition tests',
      'description' => 'Tests the node is published condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->condition = new NodeIsPublished([], '', [], $this->typedDataManager);
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
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Node is published', $this->condition->summary());
  }

  /**
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValue() {
    // We can't mock the NodeInterface because there is a bug in PHPUnit below
    // version 3.8 that causes mocking of interfaces that extend \Traversable
    // to fail. @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();

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
    // We can't mock the NodeInterface because there is a bug in PHPUnit below
    // version 3.8 that causes mocking of interfaces that extend \Traversable
    // to fail. @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();

    $node->expects($this->at(0))
      ->method('isPublished')
      ->will($this->returnValue(TRUE));

    $node->expects($this->at(1))
      ->method('isPublished')
      ->will($this->returnValue(FALSE));

    // Set the node context value.
    $this->condition->setContextValue('node', $node);

    // Test evaluation. The first invocation should return TRUE, the second
    // should return FALSE.
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());
  }

}
