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
 *       "type" = "entity:node",
 *     }
 *   }
 * )
 */
class NodeIsSticky extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return t('Node is sticky');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    return $node->isSticky();
  }

}
