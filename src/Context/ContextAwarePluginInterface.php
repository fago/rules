<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextAwarePluginInterface.
 */

namespace Drupal\rules\Context;

use \Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;

/**
 * Rules extension of ContextAwarePluginInterface.
 */
interface ContextAwarePluginInterface extends CoreContextAwarePluginInterface {

  /**
   * Refines used and provided context definitions based upon context values.
   *
   * When a plugin is configured half-way or even fully, some context values are
   * already available upon which the definition of subsequent or provided
   * context can be refined.
   */
  public function refineContextdefinitions();

}
