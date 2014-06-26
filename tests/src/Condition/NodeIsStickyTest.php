<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsStickyTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\NodeIsSticky;

/**
 * Tests the 'Node is sticky' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsSticky
 *
 * @see \Drupal\rules\Plugin\Condition\NodeIsSticky
 */
class NodeIsStickyTest extends ConditionTestBase {

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
      'name' => 'Node is sticky condition tests',
      'description' => 'Tests the node is sticky condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = new NodeIsSticky([], '', ['context' => [
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
    $this->assertEquals('Node is sticky', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $node = $this->getMock('Drupal\node\NodeInterface');
    $node->expects($this->at(0))
      ->method('isSticky')
      ->will($this->returnValue(TRUE));

    $node->expects($this->at(1))
      ->method('isSticky')
      ->will($this->returnValue(FALSE));

    // Set the node context value.
    $this->condition->setContextValue('node', $this->getMockTypedData($node));

    // Test evaluation. The first invocation should return TRUE, the second
    // should return FALSE.
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());
  }

}

