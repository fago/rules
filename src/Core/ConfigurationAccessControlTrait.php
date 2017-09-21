<?php

namespace Drupal\rules\Core;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Implements access related functions for plugins.
 */
trait ConfigurationAccessControlTrait {

  /**
   * Checks configuration permission.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user. Defaults to NULL.
   * @param bool $return_as_object
   *   (optional) Defaults to FALSE.
   *
   * @return bool|\Drupal\Core\Access\AccessResultInterface
   *   The access result. Returns a boolean if $return_as_object is FALSE (this
   *   is the default) and otherwise an AccessResultInterface object.
   *   When a boolean is returned, the result of AccessInterface::isAllowed() is
   *   returned, i.e. TRUE means access is explicitly allowed, FALSE means
   *   access is either explicitly forbidden or "no opinion".
   */
  public function checkConfigurationAccess(AccountInterface $account = NULL, $return_as_object = FALSE) {
    if (!$account) {
      $account = \Drupal::currentUser();
    }
    // We treat these as our "super-user" accesses.  We let the reaction
    // rule and component permissions control the main admin UI.
    $admin_perms = [
      'administer rules',
      'bypass rules access',
    ];

    $access = FALSE;
    foreach ($admin_perms as $perm) {
      if ($account->hasPermission($perm)) {
        $access = TRUE;
        break;
      }
    }

    if (!$access) {
      // See if the plugin has a configuration_access annotation.
      $definition = $this->getPluginDefinition();
      if (!empty($definition['configure_permissions']) && is_array($definition['configure_permissions'])) {
        foreach ($definition['configure_permissions'] as $perm) {
          if ($account->hasPermission($perm)) {
            $access = TRUE;
            break;
          }
        }
      }
    }

    if ($return_as_object) {
      return $access ? AccessResult::allowed() : AccessResult::neutral();
    }
    return $access;
  }

}
