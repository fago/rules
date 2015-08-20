<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextDefinitionInterface.
 */

namespace Drupal\rules\Context;

use \Drupal\Core\Plugin\Context\ContextDefinitionInterface as ContextDefinitionInterfaceCore;

/**
 * Context definition information required by Rules.
 *
 * The core interface is extended to add properties that are necessary for
 * Rules.
 */
interface ContextDefinitionInterface extends ContextDefinitionInterfaceCore {

  /**
   * Determines if the context value is allowed to be NULL.
   *
   * @return bool
   *   TRUE if NULL values are allowed, FALSE otherwise.
   */
  public function isAllowedNull();

  /**
   * Sets the "allow NULL value" behavior.
   *
   * @param bool $null_allowed
   *   TRUE if NULL values should be allowed, FALSE otherwise.
   *
   * @return $this
   */
  public function setAllowNull($null_allowed);

}
