<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\node\NodeInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsSticky
 * @group RulesCondition
 */
class NodeIsStickyTest extends RulesEntityIntegrationTestBase {

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
    $this->condition = $this->conditionManager->createInstance('rules_node_is_sticky');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $sticky_node = $this->prophesizeEntity(NodeInterface::class);

    $sticky_node->isSticky()->willReturn(TRUE)->shouldBeCalledTimes(1);

    // Set the node context value.
    $this->condition->setContextValue('node', $sticky_node->reveal());

    $this->assertTrue($this->condition->evaluate());

    $unsticky_node = $this->prophesizeEntity(NodeInterface::class);

    $unsticky_node->isSticky()->willReturn(FALSE)->shouldBeCalledTimes(1);

    // Set the node context value.
    $this->condition->setContextValue('node', $unsticky_node->reveal());

    $this->assertFalse($this->condition->evaluate());
  }

}
