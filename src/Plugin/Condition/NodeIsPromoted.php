<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsPromoted.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'Node is promoted' condition.
 *
 * @Condition(
 *   id = "rules_node_is_promoted",
 *   label = @Translation("Node is promoted"),
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
class NodeIsPromoted extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Node is promoted');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    return $node->isPromoted();
  }

}
