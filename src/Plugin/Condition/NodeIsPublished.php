<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsPublished.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;

/**
 * Provides a 'Node is published' condition.
 *
 * @Condition(
 *   id = "rules_node_is_published",
 *   label = @Translation("Node is published"),
 *   context = {
 *     "node" = {
 *       "label" = @Translation("Node"),
 *       "type" = "entity:node"
 *     }
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class NodeIsPublished extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return t('Node is published.');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    return $node->isPublished();
  }
}
