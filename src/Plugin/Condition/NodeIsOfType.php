<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsOfType.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'Node is of type' condition.
 *
 * @Condition(
 *   id = "rules_node_is_of_type",
 *   label = @Translation("Node is of type")
 * )
 */
class NodeIsOfType extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['node'] = ContextDefinition::create($typed_data_manager, 'entity:node')
      ->setLabel(t('Node'));

    $contexts['types'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setMultiple()
      ->setLabel(t('Content types'))
      ->setDescription(t('Check for the the allowed node types.'));

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Node is of type');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    $types = $this->getContextValue('types');
    return in_array($node->getType(), $types);
  }
}
