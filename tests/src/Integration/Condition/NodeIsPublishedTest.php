<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Condition\NodeIsPublishedTest.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsPublished
 * @group rules_conditions
 */
class NodeIsPublishedTest extends RulesEntityIntegrationTestBase {

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
    $this->condition = $this->conditionManager->createInstance('rules_node_is_published');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Node is published', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $node = $this->getMock('Drupal\node\NodeInterface');

    $node->expects($this->exactly(2))
      ->method('isPublished')
      ->will($this->onConsecutiveCalls(TRUE, FALSE));

    // Set the node context value.
    $this->condition->setContextValue('node', $node);

    // Test evaluation. The first invocation should return TRUE, the second
    // should return FALSE.
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());
  }

}
