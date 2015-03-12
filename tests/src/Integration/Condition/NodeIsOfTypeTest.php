<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Condition\NodeIsOfTypeTest.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsOfType
 * @group rules_conditions
 */
class NodeIsOfTypeTest extends RulesEntityIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->enableModule('node');
    $this->condition = $this->conditionManager->createInstance('rules_node_is_of_type');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Node is of type', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
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
