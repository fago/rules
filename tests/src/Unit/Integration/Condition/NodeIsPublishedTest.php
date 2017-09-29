<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\node\NodeInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\NodeIsPublished
 * @group RulesCondition
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
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $published_node = $this->prophesizeEntity(NodeInterface::class);

    $published_node->isPublished()->willReturn(TRUE)->shouldBeCalledTimes(1);

    // Set the node context value.
    $this->condition->setContextValue('node', $published_node->reveal());

    $this->assertTrue($this->condition->evaluate());

    $unpublished_node = $this->prophesizeEntity(NodeInterface::class);

    $unpublished_node->isPublished()->willReturn(FALSE)->shouldBeCalledTimes(1);

    // Set the node context value.
    $this->condition->setContextValue('node', $unpublished_node->reveal());

    $this->assertFalse($this->condition->evaluate());
  }

}
