<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsSticky.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides a 'Node is sticky' condition.
 *
 * @Condition(
 *   id = "rules_node_is_sticky",
 *   label = @Translation("Node is sticky"),
 *   category = @Translation("Node"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node",
 *       label = @Translation("Node")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class NodeIsSticky extends RulesConditionBase {
  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    return $node->isSticky();
  }

}
