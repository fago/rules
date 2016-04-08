<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\node\NodeInterface;
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
   * Check if the given node is sticky.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   *
   * @return bool
   *   TRUE if the node is sticky.
   */
  protected function doEvaluate(NodeInterface $node) {
    return $node->isSticky();
  }

}
