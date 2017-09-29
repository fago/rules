<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\node\NodeInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsOfType
 * @group RulesCondition
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
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $node = $this->prophesizeEntity(NodeInterface::class);
    $node->getType()->willReturn('page');

    // Set the node context value.
    $this->condition->setContextValue('node', $node->reveal());

    // Test evaluation with a list that contains the actual node type.
    $this->condition->setContextValue('types', ['page', 'article']);
    $this->assertTrue($this->condition->evaluate());

    // Test with a list that does not contain the actual node type.
    $this->condition->setContextValue('types', ['apple', 'banana']);
    $this->assertFalse($this->condition->evaluate());
  }

}
