<?php

/**
 * @file
 * Contains \Drupal\rules\Context\DataProcessorManager.
 */

namespace Drupal\rules\Context;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager for Rules data processors.
 *
 * @see \Drupal\rules\Engine\RulesDataProcessorInterface
 */
class DataProcessorManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, $plugin_definition_annotation_name = 'Drupal\rules\Annotation\RulesDataProcessor') {
    $this->alterInfo('rules_data_processor');
    parent::__construct('Plugin/RulesDataProcessor', $namespaces, $module_handler, 'Drupal\rules\Context\DataProcessorInterface', $plugin_definition_annotation_name);
  }

}
