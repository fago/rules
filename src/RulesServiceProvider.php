<?php

/**
 * @file
 * Contains \Drupal\rules\RulesServiceProvider.
 */

namespace Drupal\rules;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Swaps out the core condition manager.
 */
class RulesServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides language_manager class to test domain language negotiation.
    $definition = $container->getDefinition('plugin.manager.condition');
    $definition->setClass('Drupal\rules\Condition\ConditionManager');

    // Add in the enhanced typed data manager.
    $definition = $container->getDefinition('typed_data_manager');
    $definition->setClass('Drupal\rules\TypedData\TypedDataManager');
  }

}
