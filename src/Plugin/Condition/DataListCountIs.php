<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

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
   * Compare the value to the count of the list.
   *
   * @param array $list
   *   The list to compare the value to.
   * @param string $operator
   *   The type of comparison to do, may be one of '==', '<', or '>'.
   * @param int $value
   *   The value of that the count is to compare to.
   *
   * @return bool
   *   TRUE if the comparison returns true.
   */
  protected function doEvaluate(array $list, $operator, $value) {
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
