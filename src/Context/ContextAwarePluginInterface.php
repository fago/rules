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
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $selected_data
   *   An array of data definitions for context that is mapped using a data
   *   selector, keyed by context name.
   */
  public function refineContextDefinitions(array $selected_data);

}
