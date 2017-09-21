<?php

namespace Drupal\rules\Core;

use Drupal\Core\Session\AccountInterface;

/**
 * Defines a configuration permission control interface.
 *
 * @see \Drupal\rules\Core\ConfigurationAccessControlTrait.
 */
interface ConfigurationAccessControlInterface {

  /**
   * Check configuration access.
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
  public function checkConfigurationAccess(AccountInterface $account = NULL, $return_as_object = FALSE);

}
