<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsPublished.
 */

namespace Drupal\rules\Plugin\Condition;

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
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    return $node->isPublished();
  }

}
