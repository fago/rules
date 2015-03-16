<?php

/**
 * @file
 * Contains \Drupal\rules\Rules.
 */

namespace Drupal\rules;

/**
 * Class containing shortcuts for procedural code.
 *
 * This helpers should only be used in situations where dependencies cannot be
 * injected; e.g., in hook implementations or static methods.
 *
 * @see \Drupal
 */
class Rules {

  /**
   * Returns the Rules expression manager service.
   *
   * @return \Drupal\rules\Engine\ExpressionPluginManager
   *   The Rules expression manager service.
   */
  public static function expressionManager() {
    return \Drupal::service('plugin.manager.rules_expression');
  }

}
