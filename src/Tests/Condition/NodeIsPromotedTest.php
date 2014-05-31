<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Plugin\Condition.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests the 'Node is promoted' condition.
 */
class NodeIsPromotedTest extends EntityUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'rules'];

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorage
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Node is promoted condition tests',
      'description' => 'Tests the node is promoted condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->conditionManager = $this->container->get('plugin.manager.condition', $this->container->get('container.namespaces'));
    $this->nodeStorage = $this->entityManager->getStorage('node');

    $this->entityManager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    // Test with a promoted node.
    $node = $this->nodeStorage->create([
      'type' => 'page',
      'promote' => NODE_PROMOTED,
    ]);

    $condition = $this->conditionManager->createInstance('rules_node_is_promoted')
      ->setContextValue('node', $node);
    $this->assertTrue($condition->execute());

    // Test with an unpublished node.
    $node = $this->nodeStorage->create([
      'type' => 'page',
      'promote' => NODE_NOT_PROMOTED,
    ]);

    $condition = $this->conditionManager->createInstance('rules_node_is_promoted')
      ->setContextValue('node', $node);
    $this->assertFalse($condition->execute());
  }

}
