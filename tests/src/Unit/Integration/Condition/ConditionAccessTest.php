<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;
use Drupal\Core\Session\AccountInterface;
use Prophecy\Argument;

/**
 * Test of access control features for Rules Conditions.
 *
 * @group RulesCondition
 */
class ConditionAccessTest extends RulesIntegrationTestBase {

  /**
   * Confirm that a condition plugin respects configure_permissions.
   */
  public function testHasConfigurationAccessInfo() {
    $plugin = $this->conditionManager->createInstance('rules_test_string_condition');
    $this->assertNotNull($plugin, "The rules_test condition was found.");
    $definition = $plugin->getPluginDefinition();
    $this->assertNotEmpty($definition['configure_permissions'], "Plugin has configure permission info.");
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
    $this->assertTrue($plugin->checkConfigurationAccess(),
                      "User with permission has configure permission.");
    $this->assertTrue($plugin->checkConfigurationAccess($user_with_perm->reveal(), TRUE)->isAllowed(),
                      "Condition returns isAllowed()");

    $user_without_perm = $this->prophesize(AccountInterface::class);
    $user_without_perm
      ->hasPermission("access test configuration")
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(2);
    $user_without_perm
      ->hasPermission(Argument::type('string'))
      ->willReturn(FALSE);

    $this->assertFalse($plugin->checkConfigurationAccess($user_without_perm->reveal()),
                       "User without permission does not have configure permission.");
    $this->assertTrue($plugin->checkConfigurationAccess($user_without_perm->reveal(), TRUE)->isNeutral(),
                       "User without permission gets isNeutral().");
  }

}
