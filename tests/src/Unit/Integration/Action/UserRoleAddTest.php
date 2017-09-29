<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\UserRoleAdd
 * @group RulesAction
 */
class UserRoleAddTest extends RulesEntityIntegrationTestBase {

  /**
   * The action that is being tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->enableModule('user');
    $this->action = $this->actionManager->createInstance('rules_user_role_add');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Add user role', $this->action->summary());
  }

  /**
   * Tests adding of one role to user. User should not be saved.
   *
   * @covers ::execute
   */
  public function testAddOneRoleNoSave() {
    // Set-up a mock user.
    $account = $this->prophesizeEntity(UserInterface::class);

    $account->hasRole('administrator')->willReturn(FALSE);
    $account->addRole('administrator')->shouldBeCalledTimes(1);
    // We do noe expect call of the 'save' method because user should be
    // auto-saved later.
    $account->save()->shouldNotBeCalled();

    // Mock the 'administrator' user role.
    $administrator = $this->prophesize(RoleInterface::class);
    $administrator->id()->willReturn('administrator');

    // Test adding of one role.
    $this->action
      ->setContextValue('user', $account->reveal())
      ->setContextValue('roles', [$administrator->reveal()])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['user'], 'Action returns the user context name for auto saving.');
  }

  /**
   * Tests adding of three roles to user.
   *
   * @covers ::execute
   */
  public function testAddThreeRoles() {
    // Set-up a mock user.
    $account = $this->prophesizeEntity(UserInterface::class);
    // Mock hasRole.
    $account->hasRole('manager')->willReturn(FALSE)->shouldBeCalledTimes(1);
    $account->hasRole('editor')->willReturn(FALSE)->shouldBeCalledTimes(1);
    $account->hasRole('administrator')->willReturn(FALSE)->shouldBeCalledTimes(1);

    // Mock addRole.
    $account->addRole('manager')->shouldBeCalledTimes(1);
    $account->addRole('editor')->shouldBeCalledTimes(1);
    $account->addRole('administrator')->shouldBeCalledTimes(1);

    // Mock user roles.
    $manager = $this->prophesize(RoleInterface::class);
    $manager->id()->willReturn('manager');
    $editor = $this->prophesize(RoleInterface::class);
    $editor->id()->willReturn('editor');
    $administrator = $this->prophesize(RoleInterface::class);
    $administrator->id()->willReturn('administrator');

    // Test adding of three roles role.
    $this->action
      ->setContextValue('user', $account->reveal())
      ->setContextValue('roles', [
        $manager->reveal(),
        $editor->reveal(),
        $administrator->reveal(),
      ])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['user'], 'Action returns the user context name for auto saving.');
  }

  /**
   * Tests adding of existing role to user.
   *
   * @covers ::execute
   */
  public function testAddExistingRole() {
    // Set-up a mock user with role 'administrator'.
    $account = $this->prophesizeEntity(UserInterface::class);
    $account->hasRole('administrator')->willReturn(TRUE);

    // We do not expect a call of the 'addRole' method.
    $account->addRole(Argument::any())->shouldNotBeCalled();

    // Mock the 'administrator' user role.
    $administrator = $this->prophesize(RoleInterface::class);
    $administrator->id()->willReturn('administrator');

    // Test adding one role.
    $this->action
      ->setContextValue('user', $account->reveal())
      ->setContextValue('roles', [$administrator->reveal()])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the user has the role already.');
  }

  /**
   * Tests adding of one existing and one nonexistent role to user.
   *
   * @covers ::execute
   */
  public function testAddExistingAndNonexistentRole() {
    // Set-up a mock user with role 'administrator' but without 'editor'.
    $account = $this->prophesizeEntity(UserInterface::class);
    $account->hasRole('administrator')->willReturn(TRUE)
      ->shouldBeCalledTimes(1);
    $account->hasRole('editor')->willReturn(FALSE)
      ->shouldBeCalledTimes(1);

    // We expect only one call of the 'addRole' method.
    $account->addRole('editor')->shouldBeCalledTimes(1);

    // Mock user roles.
    $editor = $this->prophesize(RoleInterface::class);
    $editor->id()->willReturn('editor');
    $administrator = $this->prophesize(RoleInterface::class);
    $administrator->id()->willReturn('administrator');

    // Test adding one role.
    $this->action
      ->setContextValue('user', $account->reveal())
      ->setContextValue('roles', [$administrator->reveal(), $editor->reveal()])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['user'], 'Action returns the user context name for auto saving.');
  }

}
