<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\UserIsBlockedTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests the 'User is blocked' condition.
 */
class UserIsBlockedTest extends EntityUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rules'];

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
      'name' => 'User is blocked condition test',
      'description' => 'Tests the user is blocked condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();
    $this->conditionManager = $this->container->get('plugin.manager.condition', $this->container->get('container.namespaces'));
  }

  /**
   * Returns an user object for testing.
   *
   * @return \Drupal\user\UserInterface
   */
  protected function getUser($values = []) {
    // @todo: Use an entity factory once we have on instead.
    return entity_create('user', $values + [
      'name' => $this->randomName(),
      'status' => 1,
    ]);
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    // Test with a non-blocked user.
    $condition = $this->conditionManager->createInstance('rules_user_is_blocked')
      ->setContextValue('user', $this->getUser(['status' => 1]));
    $this->assertFalse($condition->execute());

    // Test with a blocked user.
    $condition = $this->conditionManager->createInstance('rules_user_is_blocked')
      ->setContextValue('user', $this->getUser(['status' => 0]));
    $this->assertTrue($condition->execute());
  }

}
