<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsOfTypeTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests the 'Node is of type' condition.
 */
class NodeIsOfTypeTest extends EntityUnitTestBase {

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
      'name' => 'Node is of type condition test',
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
    // Test a node for it's type.
    $node = $this->nodeStorage->create([
      'type' => 'page',
    ]);
    $condition = $this->conditionManager->createInstance('rules_node_is_of_type')
      ->setContextValue('node', $node)
      ->setContextValue('types', ['page', 'article']);
    $this->assertTrue($condition->execute());
  }

}
