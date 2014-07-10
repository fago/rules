<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesDataProcessorManager.
 */

namespace Drupal\rules\Plugin;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager for Rules data processors.
 *
 * @see \Drupal\rules\Engine\RulesDataProcessorInterface
 */
class RulesDataProcessorManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, $plugin_definition_annotation_name = 'Drupal\rules\Annotation\RulesDataProcessor') {
    parent::__construct('Plugin/RulesDataProcessor', $namespaces, $module_handler, $plugin_definition_annotation_name);
    $this->alterInfo('rules_data_processor');
  }

}
