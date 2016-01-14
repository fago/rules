<?php

/**
 * @file
 * Contains \Drupal\rules\RulesServiceProvider.
 */

namespace Drupal\rules;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\rules\Condition\ConditionManager;

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

    // Add in the enhanced typed data manager.
    $definition = $container->getDefinition('typed_data_manager');
    $definition->setClass('Drupal\rules\TypedData\TypedDataManager');
  }

}
