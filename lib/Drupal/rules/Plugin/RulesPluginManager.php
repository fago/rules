<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesPluginManager.
 */

namespace Drupal\rules\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\DependencyInjection\Container;

/**
 * Plugin manager for all Rules plugins.
 */
class RulesPluginManager extends DefaultPluginManager {

  /**
   * Constructs a RulesPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler) {
    $plugin_definition_annotation_name = 'Drupal\rules\Annotation\Rules';
    parent::__construct('Plugin/rules', $namespaces, $plugin_definition_annotation_name);
    $this->alterInfo($module_handler, 'rules_plugins');
  }

}
