<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesPluginTrait.
 */

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;

/**
 * Offers common methods for context plugin implementers.
 */
trait ExecutablePluginTrait {

  /**
   * {@inheritdoc}
   */
  private function getLabelValue() {
    $definition = $this->getPluginDefinition();
    if (empty($definition['label'])) {
      throw new InvalidPluginDefinitionException('The label is not defined.');
    }
    return $definition['label'];
  }

  /**
   * Get the translated summary from the label annotation.
   *
   * @throws \Drupal\Component\Plugin\Exception\ContextException
   *  Thrown if a summary was not set.
   *
   * @return string
   */
  public function summary() {
    return $this->getLabelValue();
  }

}
