<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsPublishedTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests the 'Node is published' condition.
 */
class NodeIsPublishedTest extends EntityUnitTestBase {

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
      'name' => 'Node is published condition test',
      'description' => 'Tests the condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();
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
    // Test with a published node.
    $node = $this->nodeStorage->create([
      'type' => 'page',
      'status' => 1,
    ]);

    $condition = $this->conditionManager->createInstance('rules_node_is_published')
      ->setContextValue('node', $node);
    $this->assertTrue($condition->execute());

    // Test with an unpublished node.
    $node = $this->nodeStorage->create([
      'type' => 'page',
      'status' => 0,
    ]);

    $condition = $this->conditionManager->createInstance('rules_node_is_published')
      ->setContextValue('node', $node);
    $this->assertFalse($condition->execute());
  }

}
