<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsPublished.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'Node is published' condition.
 *
 * @Condition(
 *   id = "rules_node_is_published",
 *   label = @Translation("Node is published")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class NodeIsPublished extends RulesConditionBase {

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
    return $this->t('Node is published.');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    return $node->isPublished();
  }
}
