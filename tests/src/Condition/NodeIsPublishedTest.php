<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsPublishedTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\NodeIsPublished;
use Drupal\rules\Tests\RulesTestBase;

/**
 * Tests the 'Node is published' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsPublished
 *
 * @see \Drupal\rules\Plugin\Condition\NodeIsPublished
 */
class NodeIsPublishedTest extends RulesTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

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

    $this->condition = new NodeIsPublished([], '', ['context' => [
      'node' => new ContextDefinition('entity:node'),
    ]]);

    $this->condition->setStringTranslation($this->getMockStringTranslation());
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
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $node = $this->getMock('Drupal\node\NodeInterface');
    $node->expects($this->at(0))
      ->method('isPublished')
      ->will($this->returnValue(TRUE));

    $node->expects($this->at(1))
      ->method('isPublished')
      ->will($this->returnValue(FALSE));

    // Set the node context value.
    $this->condition->setContextValue('node', $this->getMockTypedData($node));

    // Test evaluation. The first invocation should return TRUE, the second
    // should return FALSE.
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());
  }

}
