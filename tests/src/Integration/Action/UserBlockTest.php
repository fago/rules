<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\UserBlockTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\UserBlock
 * @group rules_actions
 */
class UserBlockTest extends RulesEntityIntegrationTestBase {

  /**
   * Constant used for authenticated test when mocking a user.
   */
  const AUTHENTICATED = true;

  /**
   * Constant used for authenticated test when mocking a user.
   */
  const ANONYMOUS = false;

  /**
   * Constant used for active test when mocking a user.
   */
  const ACTIVE = true;

  /**
   * Constant used for active test when mocking a user.
   */
  const BLOCKED = false;

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $action;

  /**
   * The mocked session manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->enableModule('user');

    $this->sessionManager = $this->getMock('Drupal\Core\Session\SessionManagerInterface');
    $this->container->set('session_manager', $this->sessionManager);
    $this->action = $this->actionManager->createInstance('rules_user_block');
  }

  /**
   * Test the summary method.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Block a user', $this->action->summary());
  }

  /**
   * Test execute() method for active and authenticated users.
   *
   * @covers ::execute
   */
  public function testBlockUserWithValidUser() {
    $user = $this->getUserMock(self::ACTIVE, self::AUTHENTICATED);

    $user->expects($this->once())
      ->method('block');

    $user->expects($this->once())
      ->method('id')
      ->willReturn('123');

    $this->sessionManager->expects($this->once())
      ->method('delete')
      ->with('123');

    $this->action->setContextValue('user', $user);

    $this->action->execute();
  }

  /**
   * Test execute() method for active and anonymous users.
   *
   * @covers ::execute
   */
  public function testBlockUserWithActiveAnonymousUser() {
    $user = $this->getUserMock(self::ACTIVE, self::ANONYMOUS);

    $user->expects($this->never())
      ->method('block');

    $this->sessionManager->expects($this->never())
      ->method('delete');

    $this->action->setContextValue('user', $user);

    $this->action->execute();
  }


  /**
   * Test execute() method for blocked and authenticated users.
   *
   * @covers ::execute
   */
  public function testBlockUserWithBlockedAuthenticatedUser() {
    $user = $this->getUserMock(self::BLOCKED, self::AUTHENTICATED);

    $user->expects($this->never())
      ->method('block');

    $this->sessionManager->expects($this->never())
      ->method('delete');

    $this->action->setContextValue('user', $user);

    $this->action->execute();
  }

  /**
   * Test execute() method for blocked and anonymous users.
   *
   * @covers ::execute
   */
  public function testBlockUserWithBlockedAnonymousUser() {
    $user = $this->getUserMock(self::BLOCKED, self::ANONYMOUS);

    $user->expects($this->never())
      ->method('block');

    $this->sessionManager->expects($this->never())
      ->method('delete');

    $this->action->setContextValue('user', $user);

    $this->action->execute();
  }

  /**
   * Creates a mock user.
   *
   * @param bool $active
   *   Is user activated.
   * @param bool $authenticated
   *   Is user authenticated.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\user\UserInterface
   *   The mocked user object.
   */
  protected function getUserMock($active, $authenticated) {
    $user = $this->getMock('Drupal\user\UserInterface');

    $user->expects($this->any())
      ->method('isActive')
      ->willReturn($active);

    $user->expects($this->any())
      ->method('isAuthenticated')
      ->willReturn($authenticated);

    return $user;
  }

}
