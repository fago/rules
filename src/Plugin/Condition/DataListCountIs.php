<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\DataListCountIs.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'List count comparison' condition.
 *
 * @Condition(
 *   id = "rules_list_count_is",
 *   label = @Translation("List Count Comparison"),
 *   category = @Translation("Data"),
 *   context = {
 *     "list" = @ContextDefinition("list",
 *       label = @Translation("List"),
 *       description = @Translation("A multi value data element to have its count compared, specified by using a data selector, eg 'node:uid:entity:roles'.")
 *     ),
 *     "operator" = @ContextDefinition("string",
 *       label = @Translation("Operator"),
 *       description = @Translation("The comparison operator.")
 *     ),
 *     "value" = @ContextDefinition("integer",
 *       label = @Translation("Count"),
 *       description = @Translation("The count to compare the data count with.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 * @todo: set ContextDefinition default value
 * @todo: set ContextDefinition options list
 * @todo: set ContextDefinition restriction
 */
class DataListCountIs extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('List count comparison');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $list = $this->getContextValue('list');
    $operator = $this->getContextValue('operator');
    $value = $this->getContextValue('value');

    switch ($operator) {
      case '==':
        return count($list) == $value;

      case '<';
        return count($list) < $value;

      case '>';
        return count($list) > $value;

    }
  }

}
