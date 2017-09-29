<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;
use Drupal\Core\Session\AccountInterface;
use Prophecy\Argument;

/**
 * Tests configuration access control for Rules Actions.
 *
 * @group RulesAction
 */
class RulesActionAccessTest extends RulesIntegrationTestBase {

  /**
   * Confirm that a condition plugin respects configure permission.
   */
  public function testHasConfigurationAccessInfo() {
    $plugin = $this->actionManager->createInstance('rules_test_string');
    $definition = $plugin->getPluginDefinition();
    $this->assertNotEmpty($definition['configure_permissions'], "Plugin has configuration permission info.");
    $perms = $definition['configure_permissions'];
    $this->assertTrue(is_array($perms), "configure_permissions is an array");
    $this->assertContains("access test configuration", $perms, "Expected permission found in configure_permissions.");

    // Now see if the permission is actually used.
    $user_with_perm = $this->prophesize(AccountInterface::class);
    $user_with_perm
      ->hasPermission("access test configuration")
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(2);
    $user_with_perm
      ->hasPermission(Argument::type('string'))
      ->willReturn(FALSE);

    $this->container->set('current_user', $user_with_perm->reveal());
    $this->assertTrue($plugin->checkConfigurationAccess(), "User with permission has configuration access.");

    $object_result = $plugin->checkConfigurationAccess($user_with_perm->reveal(), TRUE);
    $this->assertTrue($object_result->isAllowed(), "AccessResult in allowed state if an object is requested.");

    $user_without_perm = $this->prophesize(AccountInterface::class);
    $user_without_perm
      ->hasPermission("access test configuration")
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(2);
    $user_without_perm
      ->hasPermission(Argument::type('string'))
      ->willReturn(FALSE);

    $this->assertFalse($plugin->checkConfigurationAccess($user_without_perm->reveal()),
                       "User without permission does not have configuration access.");
    $object_result = $plugin->checkConfigurationAccess($user_without_perm->reveal(), TRUE);
    $this->assertTrue($object_result->isNeutral(), "an AccessResultNeutral object is returned on not allowed if an object is requested.");

  }

}
