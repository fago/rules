<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsPromoted.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'Node is promoted' condition.
 *
 * @Condition(
 *   id = "rules_node_is_promoted",
 *   label = @Translation("Node is promoted")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class NodeIsPromoted extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions() {
    $contexts['node'] = ContextDefinition::create('entity:node')
      ->setLabel(t('Node'));

    return $contexts;
  }

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
