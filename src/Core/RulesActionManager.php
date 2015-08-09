<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesActionManager.
 */

namespace Drupal\rules\Core;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\CategorizingPluginManagerTrait;
use Drupal\Core\Plugin\Context\ContextAwarePluginManagerTrait;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an Action plugin manager for the Rules actions API.
 *
 * @see \Drupal\Core\Annotation\Action
 * @see \Drupal\Core\Action\ActionInterface
 * @see \Drupal\Core\Action\ActionBase
 * @see plugin_api
 */
class RulesActionManager extends DefaultPluginManager implements RulesActionManagerInterface {

  use CategorizingPluginManagerTrait;
  use ContextAwarePluginManagerTrait;

  /**
   * Constructs a new class instance.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/RulesAction', $namespaces, $module_handler, 'Drupal\rules\Core\RulesActionInterface', 'Drupal\rules\Core\Annotation\RulesAction');
    $this->alterInfo('rules_action_info');
    $this->setCacheBackend($cache_backend, 'rules_action_info');
  }

}
