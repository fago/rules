<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityIsOfTypeTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests the 'Entity is of type' condition.
 */
class EntityIsOfTypeTest extends EntityUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rules', 'node'];

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
    return [
      'name' => 'Entity is of type condition test',
      'description' => 'Tests that an entity is of a particular type.',
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
    // Test with a node of type "page."
    $node = $this->nodeStorage->create([
      'type' => 'page',
    ]);

    // Add the test node to our context as the evaluated entity, along with an
    //  explicit entity type string.
    // First, test with a value that should evaluate TRUE.
    $condition = $this->conditionManager->createInstance('rules_entity_is_of_type')
      ->setContextValue('entity', $node)
      ->setContextValue('type', 'node');
    $this->assertTrue($condition->execute());

    // Then test with values that should evaluate FALSE.
    $condition->setContextValue('type', 'taxonomy_term');
    $this->assertFalse($condition->execute());
  }
}