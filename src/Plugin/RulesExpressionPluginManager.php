<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpressionPluginManager.
 */

namespace Drupal\rules\Plugin;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager for all Rules expressions.
 *
 * @see \Drupal\rules\Engine\RulesExpressionInterface
 */
class RulesExpressionPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, $plugin_definition_annotation_name = 'Drupal\rules\Annotation\RulesExpression') {
    $this->alterInfo('rules_expression');
    parent::__construct('Plugin/RulesExpression', $namespaces, $module_handler, 'Drupal\rules\Engine\RulesExpressionInterface', $plugin_definition_annotation_name);
  }

}
