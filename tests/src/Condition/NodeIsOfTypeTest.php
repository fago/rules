<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsOfTypeTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\NodeIsOfType;
use Drupal\rules\Tests\RulesUnitTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsOfType
 * @group rules_conditions
 */
class NodeIsOfTypeTest extends RulesUnitTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = new NodeIsOfType([], '', ['context' => [
      'node' => new ContextDefinition('entity:node'),
      'types' => new ContextDefinition('string', NULL, TRUE, TRUE),
    ]]);

    $this->condition->setStringTranslation($this->getMockStringTranslation());
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
    $this->condition->setContextValue('node', $this->getMockTypedData($node));

    // Test evaluation with a list that contains the actual node type.
    $this->condition->setContextValue('types', $this->getMockTypedData(['page', 'article']));
    $this->assertTrue($this->condition->evaluate());

    // Test with a list that does not contain the actual node type.
    $this->condition->setContextValue('types', $this->getMockTypedData(['apple', 'banana']));
    $this->assertFalse($this->condition->evaluate());
  }

}
