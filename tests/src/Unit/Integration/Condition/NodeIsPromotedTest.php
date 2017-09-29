<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\node\NodeInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsPromoted
 * @group RulesCondition
 */
class NodeIsPromotedTest extends RulesEntityIntegrationTestBase {

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
    $this->condition = $this->conditionManager->createInstance('rules_node_is_promoted');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $promoted_node = $this->prophesizeEntity(NodeInterface::class);

    $promoted_node->isPromoted()->willReturn(TRUE)->shouldBeCalledTimes(1);

    // Set the node context value.
    $this->condition->setContextValue('node', $promoted_node->reveal());

    $this->assertTrue($this->condition->evaluate());

    $unpromoted_node = $this->prophesizeEntity(NodeInterface::class);

    $unpromoted_node->isPromoted()->willReturn(FALSE)->shouldBeCalledTimes(1);

    // Set the node context value.
    $this->condition->setContextValue('node', $unpromoted_node->reveal());

    $this->assertFalse($this->condition->evaluate());
  }

}
