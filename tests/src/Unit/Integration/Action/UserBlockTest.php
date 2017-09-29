<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
use Drupal\user\UserInterface;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\UserBlock
 * @group RulesAction
 */
class UserBlockTest extends RulesEntityIntegrationTestBase {

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
   * The mocked session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $sessionManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->enableModule('user');

    $this->sessionManager = $this->prophesize(SessionManagerInterface::class);
    $this->container->set('session_manager', $this->sessionManager->reveal());
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

    $user->block()->shouldBeCalledTimes(1);

    $user->id()->willReturn('123')->shouldBeCalledTimes(1);

    $this->sessionManager->delete('123')->shouldBeCalledTimes(1);

    $this->action->setContextValue('user', $user->reveal());

    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['user'], 'Action returns the user context name for auto saving.');
  }

  /**
   * Test execute() method for active and anonymous users.
   *
   * @covers ::execute
   */
  public function testBlockUserWithActiveAnonymousUser() {
    $user = $this->getUserMock(self::ACTIVE, self::ANONYMOUS);

    $user->block()->shouldNotBeCalled();

    $this->sessionManager->delete()->shouldNotBeCalled();

    $this->action->setContextValue('user', $user->reveal());

    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the user has not been altered.');
  }

  /**
   * Test execute() method for blocked and authenticated users.
   *
   * @covers ::execute
   */
  public function testBlockUserWithBlockedAuthenticatedUser() {
    $user = $this->getUserMock(self::BLOCKED, self::AUTHENTICATED);

    $user->block()->shouldNotBeCalled();

    $this->sessionManager->delete()->shouldNotBeCalled();

    $this->action->setContextValue('user', $user->reveal());

    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the user has not been altered.');
  }

  /**
   * Test execute() method for blocked and anonymous users.
   *
   * @covers ::execute
   */
  public function testBlockUserWithBlockedAnonymousUser() {
    $user = $this->getUserMock(self::BLOCKED, self::ANONYMOUS);

    $user->block()->shouldNotBeCalled();

    $this->sessionManager->delete()->shouldNotBeCalled();

    $this->action->setContextValue('user', $user->reveal());

    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the user has not been altered.');
  }

  /**
   * Creates a mock user.
   *
   * @param bool $active
   *   Is user activated.
   * @param bool $authenticated
   *   Is user authenticated.
   *
   * @return \Drupal\user\UserInterface|\Prophecy\Prophecy\ProphecyInterface
   *   The mocked user object.
   */
  protected function getUserMock($active, $authenticated) {
    $user = $this->prophesizeEntity(UserInterface::class);

    $user->isActive()->willReturn($active);
    $user->isAuthenticated()->willReturn($authenticated);

    return $user;
  }

}
