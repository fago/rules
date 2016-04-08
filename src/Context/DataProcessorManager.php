<?php

namespace Drupal\rules\Context;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\rules\Annotation\RulesDataProcessor;

/**
 * Plugin manager for Rules data processors.
 *
 * @see \Drupal\rules\Context\DataProcessorInterface
 */
class DataProcessorManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, $plugin_definition_annotation_name = RulesDataProcessor::class) {
    $this->alterInfo('rules_data_processor');
    parent::__construct('Plugin/RulesDataProcessor', $namespaces, $module_handler, DataProcessorInterface::class, $plugin_definition_annotation_name);
  }

}
