<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\DataFilterManager.
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\rules\TypedData\Annotation\DataFilter;

/**
 * Manager for data filter plugins.
 *
 * @see \Drupal\rules\TypedData\DataFilterInterface
 */
class DataFilterManager extends DefaultPluginManager implements DataFilterManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, $plugin_definition_annotation_name = DataFilter::class) {
    $this->alterInfo('typed_data_filter');
    parent::__construct('Plugin/TypedDataFilter', $namespaces, $module_handler, DataFilterInterface::class, $plugin_definition_annotation_name);
  }

}
