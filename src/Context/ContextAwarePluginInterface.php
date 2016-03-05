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
   * Note that context gets refined at configuration and execution time of the
   * plugin.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $selected_data
   *   An array of data definitions for context that is mapped using a data
   *   selector, keyed by context name.
   */
  public function refineContextDefinitions(array $selected_data);

  /**
   * Asserts additional metadata for the selected data.
   *
   * Allows the plugin to assert additional metadata that is in place when the
   * plugin has been successfully executed. A typical use-case would be
   * asserting the node type for a "Node is of type" condition. By doing so,
   * sub-sequent executed plugins are aware of the metadata and can build upon
   * it.
   *
   * Note that metadata is only asserted on configuration time. The plugin has
   * to ensure that the run-time data matches the asserted configuration if it
   * has been executed successfully.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $selected_data
   *   An array of data definitions for context that is mapped using a data
   *   selector, keyed by context name.
   */
  public function assertMetadata(array $selected_data);

}
