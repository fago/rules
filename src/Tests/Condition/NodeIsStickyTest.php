<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\NodeIsStickyTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests the 'Node is sticky' condition.
 */
class NodeIsStickyTest extends EntityUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('node', 'rules');

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
    return array(
      'name' => 'Node is sticky condition test',
      'description' => 'Tests the node is sticky condition.',
      'group' => 'Rules conditions',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();
    $this->conditionManager = $this->container->get('plugin.manager.condition', $this->container->get('container.namespaces'));
    $this->nodeStorage = $this->entityManager->getStorage('node');

    $this->entityManager->getStorage('node_type')
      ->create(array('type' => 'page'))
      ->save();
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    // Test with a sticky node.
    $node = $this->nodeStorage->create(array(
      'type' => 'page',
      'sticky' => 1,
    ));

    $condition = $this->conditionManager->createInstance('rules_node_is_sticky')
      ->setContextValue('node', $node);
    $this->assertTrue($condition->execute());

    // Test with an non-sticky node.
    $node = $this->nodeStorage->create(array(
      'type' => 'page',
      'sticky' => 0,
    ));

    $condition = $this->conditionManager->createInstance('rules_node_is_sticky')
      ->setContextValue('node', $node);
    $this->assertFalse($condition->execute());
  }

}
