<?php

namespace Drupal\rules\Context;

use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;

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
   * Implement this method, when the plugin's context definitions need to be
   * refined. When the selected data definitions should be refined, implement
   * ::assertMetadata() instead.
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
   * Implement this method, when the selected data definitions need to be
   * refined. When the plugin's context definitions should be refined, implement
   * ::refineContextDefinitions() instead.
   *
   * Note that metadata is only asserted on configuration time. The plugin has
   * to ensure that the run-time data matches the asserted configuration if it
   * has been executed successfully.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $selected_data
   *   An array of data definitions for context that is mapped using a data
   *   selector, keyed by context name.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   An array of modified data definitions, keyed as the passed array. Note
   *   data definitions need to be cloned *before* they are modified, such that
   *   the changes do not propagate unintentionally.
   */
  public function assertMetadata(array $selected_data);

}
