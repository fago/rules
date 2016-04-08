<?php

namespace Drupal\rules;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\rules\Core\ConditionManager;

/**
 * Swaps out the core condition manager.
 */
class RulesServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides the core condition plugin manager service with our own.
    $definition = $container->getDefinition('plugin.manager.condition');
    $definition->setClass(ConditionManager::class);
  }

}
