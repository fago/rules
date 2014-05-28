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
   * The node type used for testing.
   *
   * @var \Drupal\node\Entity\NodeType
   */
  protected $nodeType;

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
    $entity_manager = $this->container->get('entity.manager');
    $this->conditionManager = $this->container->get('plugin.manager.condition', $this->container->get('container.namespaces'));
    $this->nodeStorage = $entity_manager->getStorage('node');
    $this->nodeType = $entity_manager->getStorage('node_type')
      ->create('node_type', array('type' => 'page'));
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    // Test with a sticky node.
    $node = $this->nodeStorage->create(array(
      'type' => $this->nodeType->bundle(),
      'sticky' => 1,
    ));

    $condition = $this->conditionManager->createInstance('rules_node_is_sticky')
      ->setContextValue('node', $node);
    $this->assertTrue($condition->execute());

    // Test with an non-sticky node.
    $node = $this->nodeStorage->create(array(
      'type' => $this->nodeType->bundle(),
      'sticky' => 0,
    ));

    $condition = $this->conditionManager->createInstance('rules_node_is_sticky')
      ->setContextValue('node', $node);
    $this->assertFalse($condition->execute());
  }

}
