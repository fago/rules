<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesDataProcessor\NumericOffset.
 */

namespace Drupal\rules\Plugin\RulesDataProcessor;

use Drupal\Core\Plugin\PluginBase;
use Drupal\rules\Engine\RulesDataProcessorInterface;

/**
 * A data processor for applying numerical offsets.
 *
 * The plugin configuration must contain the following entry:
 * - offset: the value that should be added.
 *
 * @RulesDataProcessor(
 *   id = "rules_numeric_offset",
 *   label = @Translation("Apply numeric offset"),
 *   types = {"integer", "float"}
 * )
 */
class NumericOffset extends PluginBase implements RulesDataProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function process($value) {
    return $value + $this->configuration['offset'];
  }

}
