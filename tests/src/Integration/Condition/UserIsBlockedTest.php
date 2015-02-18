<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Condition\UserIsBlockedTest.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserIsBlocked
 * @group rules_conditions
 */
class UserIsBlockedTest extends RulesEntityIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->enableModule('user');
    $this->condition = $this->conditionManager->createInstance('rules_user_is_blocked');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('User is blocked', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $user = $this->getMock('Drupal\user\UserInterface');
    $user->expects($this->at(0))
      ->method('isBlocked')
      ->will($this->returnValue(TRUE));

    $user->expects($this->at(1))
      ->method('isBlocked')
      ->will($this->returnValue(FALSE));

    // Set the user context value.
    $this->condition->setContextValue('user', $user);

    // Test evaluation. The first invocation should return TRUE, the second
    // should return FALSE.
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());
  }

}
