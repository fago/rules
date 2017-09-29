<?php

namespace Drupal\Tests\rules\Unit\Integration\RulesAction;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;
use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Session\AccountInterface;
use Prophecy\Argument;

/**
 * Tests access control for the configuration interface of Rules plugins.
 *
 * @group Rules
 */
class RulesAdminAccessTest extends RulesIntegrationTestBase {

  /**
   * Test administrative access to plugin configuration UI.
   */
  public function testRespectsAdminPermissions() {
    // 3 user classes to test.
    $super_admin = $this->prophesize(AccountInterface::class);
    $super_admin->hasPermission(Argument::any())->willReturn(TRUE);

    $power_user = $this->prophesize(AccountInterface::class);
    $power_user->hasPermission('admin this plugin')->willReturn(TRUE);
    $power_user->hasPermission(Argument::any())->willReturn(FALSE);

    $joe_user = $this->prophesize(AccountInterface::class);
    $joe_user->hasPermission(Argument::any())->willReturn(FALSE);

    // Our plug-in will behave as if it has the annotation property
    // 'configuration_access'. getPluginDefinition should be called only
    // twice, since the super admin should get approval before it is called.
    // I use getMockBuilder since I need the actual code from the
    // RulesActionBase class for the test.
    $action = $this->getMockBuilder(RulesActionBase::class)
      ->disableOriginalConstructor()
      ->setMethods(['getPluginDefinition'])
      ->getMockForAbstractClass();

    $action
      ->expects($this->exactly(2))
      ->method('getPluginDefinition')
      ->willReturn([
        'plugin_id' => 'some_action',
        'configure_permissions' => ['admin this plugin'],
      ]);

    $user = $super_admin->reveal();
    $this->assertTrue($action->checkConfigurationAccess($user), "Super-user has admin access");

    $user = $power_user->reveal();
    $this->assertTrue($action->checkConfigurationAccess($user), "Power-user has admin access");

    $user = $joe_user->reveal();
    $this->assertFalse($action->checkConfigurationAccess($user), "Ordinary user lacks admin access");

  }

}
