<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\node\NodeInterface;
use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides a 'Node is published' condition.
 *
 * @Condition(
 *   id = "rules_node_is_published",
 *   label = @Translation("Node is published"),
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
class NodeIsPublished extends RulesConditionBase {

  /**
   * Checks if a node is published.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   *
   * @return bool
   *   TRUE if the node is published.
   */
  protected function doEvaluate(NodeInterface $node) {
    return $node->isPublished();
  }

}
