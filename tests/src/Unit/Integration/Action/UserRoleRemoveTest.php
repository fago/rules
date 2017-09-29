<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\UserRoleRemove
 * @group RulesAction
 */
class UserRoleRemoveTest extends RulesEntityIntegrationTestBase {

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
    $this->action = $this->actionManager->createInstance('rules_user_role_remove');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Remove user role', $this->action->summary());
  }

  /**
   * Tests removing role from user. User should not be saved.
   *
   * @covers ::execute
   */
  public function testRemoveExistingRoleNoSave() {

    // Set-up a mock user with role 'editor'.
    $account = $this->prophesizeEntity(UserInterface::class);
    $account->hasRole('editor')->willReturn(TRUE);
    $account->removeRole('editor')->shouldBeCalledTimes(1);

    // We do not expect call of the 'save' method because user should be
    // auto-saved later.
    $account->save()->shouldNotBeCalled();

    // Mock the 'editor' user role.
    $editor = $this->prophesize(RoleInterface::class);
    $editor->id()->willReturn('editor');

    // Test removing of one role.
    $this->action
      ->setContextValue('user', $account->reveal())
      ->setContextValue('roles', [$editor->reveal()])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['user'], 'Action returns the user context name for auto saving.');
  }

  /**
   * Tests removing non-existing role from user.
   *
   * @covers ::execute
   */
  public function testRemoveNonExistingRole() {

    // Set-up a mock user with role 'editor'.
    $account = $this->prophesizeEntity(UserInterface::class);
    $account->hasRole('editor')->willReturn(FALSE);
    $account->removeRole('editor')->shouldNotBeCalled();

    // Mock the 'editor' user role.
    $editor = $this->prophesize(RoleInterface::class);
    $editor->id()->willReturn('editor');

    // Test removing of one role.
    $this->action
      ->setContextValue('user', $account->reveal())
      ->setContextValue('roles', [$editor->reveal()])
      ->execute();

    $this->assertNotEquals($this->action->autoSaveContext(), ['user'], 'Action returns the user context name for auto saving.');
  }

}
