<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\UserUnblockTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\UserUnblock
 * @group rules_actions
 */
class UserUnblockTest extends RulesEntityIntegrationTestBase {

  /**
   * Constant used for authenticated test when mocking a user.
   */
  const AUTHENTICATED = TRUE;

  /**
   * Constant used for authenticated test when mocking a user.
   */
  const ANONYMOUS = FALSE;

  /**
   * Constant used for active test when mocking a user.
   */
  const ACTIVE = TRUE;

  /**
   * Constant used for active test when mocking a user.
   */
  const BLOCKED = FALSE;

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->enableModule('user');
    $this->action = $this->actionManager->createInstance('rules_user_unblock');
  }

  /**
   * Test the summary method.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Unblock a user', $this->action->summary());
  }

  /**
   * Test execute() method for users with different status.
   * @dataProvider userProvider
   * @covers ::execute
   */
  public function testUnblockUser($active, $authenticated, $expects, $context_name) {
    // Set-up a mock user.
    $account = $this->getMock('Drupal\user\UserInterface');
    // Mock isBlocked.
    $account->expects($this->any())
      ->method('isBlocked')
      ->willReturn(!$active);
    // Mock isAuthenticated.
    $account->expects($this->any())
      ->method('isAuthenticated')
      ->willReturn($authenticated);
    // Mock activate.
    $account->expects($this->{$expects}())
      ->method('activate');
    // We do noe expect call of the 'save' method because user should be
    // auto-saved later.
    $account->expects($this->never())
      ->method('save');
    // Test unblocking the user.
    $this->action
      ->setContextValue('user', $account)
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), $context_name, 'Action returns correct context name for auto saving.');
  }

  /**
   * Data provider for ::testUnblockUser.
   */
  public function userProvider() {
    return [
      // Test blocked authenticated user.
      [self::BLOCKED, self::AUTHENTICATED, 'once', ['user']],
      // Test active anonymous user.
      [self::ACTIVE, self::ANONYMOUS, 'never', []],
      // Test active authenticated user.
      [self::ACTIVE, self::AUTHENTICATED, 'never', []],
      // Test blocked anonymous user.
      [self::BLOCKED, self::ANONYMOUS, 'never', []],
    ];
  }

}
