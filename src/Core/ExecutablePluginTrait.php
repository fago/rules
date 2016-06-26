<?php

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;

/**
 * Offers common methods for executable plugins.
 */
trait ExecutablePluginTrait {

  /**
   * Get the translated label from the plugin definition.
   *
   * @return string
   *   The label of the plugin.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the label is not defined for the plugin.
   */
  protected function getLabelValue() {
    $definition = $this->getPluginDefinition();
    if (empty($definition['label'])) {
      throw new InvalidPluginDefinitionException('The label is not defined for plugin ' . $this->getPluginId() . ' (' . __CLASS__ . ').');
    }
    // The label can be an instance of
    // \Drupal\Core\StringTranslation\TranslationWrapper here, so make sure to
    // always return a primitive string representation here.
    return (string) $definition['label'];
  }

  /**
   * Get the translated summary from the label annotation.
   *
   * @return string
   *   The summary of the plugin.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if a summary was not set.
   */
  public function summary() {
    return $this->getLabelValue();
  }

}
