<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\UserRoleAddTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;
use Drupal\Tests\rules\Integration\RulesUserIntegrationTestTrait;
use Drupal\user\RoleInterface;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\UserRoleAdd
 * @group rules_actions
 */
class UserRoleAddTest extends RulesEntityIntegrationTestBase {

  use RulesUserIntegrationTestTrait;

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
    $account = $this->getMockedUser();
    $account->expects($this->once())
      ->method('hasRole')
      ->with($this->equalTo('administrator'))
      ->will($this->returnValue(FALSE));
    $account->expects($this->once())
      ->method('addRole')
      ->with($this->equalTo('administrator'));
    // We do noe expect call of the 'save' method because user should be
    // auto-saved later.
    $account->expects($this->never())
      ->method('save');

    // Mock the 'administrator' user role.
    $administrator = $this->getMockedUserRole('administrator');

    // Test adding of one role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$administrator])
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
    $account = $this->getMockedUser();
    // Mock hasRole.
    $account->expects($this->exactly(3))
      ->method('hasRole')
      ->with($this->logicalOr(
        $this->equalTo('manager'),
        $this->equalTo('editor'),
        $this->equalTo('administrator')
      ))
      ->will($this->returnValue(FALSE));
    // Mock addRole.
    $account->expects($this->exactly(3))
      ->method('addRole')
      ->with($this->logicalOr(
        $this->equalTo('manager'),
        $this->equalTo('editor'),
        $this->equalTo('administrator')
      ));

    // Mock user roles.
    $manager = $this->getMockedUserRole('manager');
    $editor = $this->getMockedUserRole('editor');
    $administrator = $this->getMockedUserRole('administrator');

    // Test adding of three roles role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$manager, $editor, $administrator])
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
    $account = $this->getMockedUser();
    $account->expects($this->once())
      ->method('hasRole')
      ->with($this->equalTo('administrator'))
      ->will($this->returnValue(TRUE));

    // We do not expect a call of the 'addRole' method.
    $account->expects($this->never())
      ->method('addRole');

    // Mock the 'administrator' user role.
    $administrator = $this->getMockedUserRole('administrator');

    // Test adding one role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$administrator])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the user has been saved already.');
  }

  /**
   * Tests adding of one existing and one nonexistent role to user.
   *
   * @covers ::execute
   */
  public function testAddExistingAndNonexistentRole() {
    // Set-up a mock user with role 'administrator' but without 'editor'.
    $account = $this->getMockedUser();
    $account->expects($this->exactly(2))
      ->method('hasRole')
      ->with($this->logicalOr(
        $this->equalTo('editor'),
        $this->equalTo('administrator')
      ))
      ->will($this->returnCallback(
        function($rid) {
          if ($rid == 'administrator') {
            return TRUE;
          }
          return FALSE;
        }
      ));

    // We expect only one call of the 'addRole' method.
    $account->expects($this->once())
      ->method('addRole');

    // Mock user roles.
    $editor = $this->getMockedUserRole('editor');
    $administrator = $this->getMockedUserRole('administrator');

    // Test adding one role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$administrator, $editor])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['user'], 'Action returns the user context name for auto saving.');
  }

  /**
   * Tests adding of the 'anonymous' role to user.
   *
   * @expectedException \InvalidArgumentException
   */
  function testAddAnonymousRole() {
    // Set-up a mock user.
    $account = $this->getMockedUser();
    // If you try to add anonymous or authenticated role to user, Drupal will
    // throw an \InvalidArgumentException. Anonymous or authenticated role ID
    // must not be assigned manually.
    $account->expects($this->once())
      ->method('hasRole')
      ->with($this->logicalOr(
        $this->equalTo(RoleInterface::ANONYMOUS_ID)
      ))
      ->will($this->returnCallback(
        function($rid) {
          if (in_array($rid, [RoleInterface::AUTHENTICATED_ID, RoleInterface::ANONYMOUS_ID])) {
            throw new \InvalidArgumentException('Anonymous or authenticated role ID must not be assigned manually.');
          }
        }
      ));

    // Mock the 'anonymous' user role.
    $anonymous = $this->getMockedUserRole(RoleInterface::ANONYMOUS_ID);

    // Test adding of the 'anonymous' role.
    $this->action
      ->setContextValue('user', $account)
      ->setContextValue('roles', [$anonymous])
      ->execute();

    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the user has been saved already.');
  }

}
