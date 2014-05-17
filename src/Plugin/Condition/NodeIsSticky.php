<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsSticky.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;

/**
 * Provides a 'Node is sticky' condition.
 *
 * @Condition(
 *   id = "rules_node_is_sticky",
 *   label = @Translation("Node is sticky"),
 *   context = {
 *     "node" = {
 *       "type" = "entity",
 *       "constraints" = {
 *         "EntityType" = "node"
 *       }
 *     }
 *   }
 * )
 */
class NodeIsSticky extends ConditionPluginBase {

  /**
   * Implements \Drupal\Core\Executable\ExecutableInterface::summary().
   */
  public function summary() {
    return t('Node is sticky');
  }

  /**
   * Implements \Drupal\condition\ConditionInterface::evaluate().
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    return $node->sticky == 1;
  }

}
