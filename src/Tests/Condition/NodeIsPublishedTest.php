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
  public static $modules = array('rules');

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Node is published condition test',
      'description' => 'Tests the condition.',
      'group' => 'Rules conditions',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();
    $this->conditionManager = $this->container->get('plugin.manager.condition', $this->container->get('container.namespaces'));
    $this->nodeType = entity_create('node_type', array('type' => 'page'));
  }

  /**
   * Returns a node object for testing.
   *
   * @param array $values
   *   An array of values to create the node with.
   *
   * @return \Drupal\Node\NodeInterface
   *   The created node.
   */
  protected function getNode($values = array()) {
    // @todo: Use an entity factory once we have on instead.
    return entity_create('node', $values + array(
      'title' => $this->randomName(),
      'type' => $this->nodeType->type,
      'status' => 1,
    ));
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    // Test with a published node.
    $condition = $this->conditionManager->createInstance('rules_node_is_published')
      ->setContextValue('node', $this->getNode(array('status' => 1)));
    $this->assertTrue($condition->execute());

    // Test with an unpublished node.
    $condition = $this->conditionManager->createInstance('rules_node_is_published')
      ->setContextValue('node', $this->getNode(array('status' => 0)));
    $this->assertFalse($condition->execute());
  }

}
