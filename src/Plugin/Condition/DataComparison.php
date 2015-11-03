<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\DataComparison.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides a 'Data comparison' condition.
 *
 * @Condition(
 *   id = "rules_data_comparison",
 *   label = @Translation("Data comparison"),
 *   category = @Translation("Data"),
 *   context = {
 *     "data" = @ContextDefinition("any",
 *       label = @Translation("Data to compare"),
 *       description = @Translation("The data to be checked to be empty, specified by using a data selector, e.g. 'node:uid:entity:name:value'.")
 *     ),
 *     "operator" = @ContextDefinition("string",
 *       label = @Translation("Operator"),
 *       description = @Translation("The comparison operator."),
 *       default_value = "==",
 *       required = FALSE
 *     ),
 *     "value" = @ContextDefinition("any",
 *        label = @Translation("Data value"),
 *        description = @Translation("The value to compare the data with.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Find a way to port rules_condition_data_is_operator_options() from Drupal 7.
 */
class DataComparison extends RulesConditionBase {

  /**
   * Evaluate the data comparison.
   *
   * @param mixed $data
   *   Supplied data to test.
   * @param string $operator
   *   Data comparison operator. Typically one of:
   *     - "=="
   *     - "<"
   *     - ">"
   *     - "contains" (for strings or arrays)
   *     - "IN" (for arrays or lists).
   * @param mixed $value
   *   The value to be compared against $data.
   *
   * @return bool
   *   The evaluation of the condition.
   */
  protected function doEvaluate($data, $operator, $value) {
    $operator = $operator ? $operator : '==';
    switch ($operator) {
      case '<':
        return $data < $value;

      case '>':
        return $data > $value;

      case 'contains':
        return is_string($data) && strpos($data, $value) !== FALSE || is_array($data) && in_array($value, $data);

      case 'IN':
        return is_array($value) && in_array($data, $value);

      default:
        // In case both values evaluate to FALSE, further differentiate between
        // NULL values and values evaluating to FALSE.
        if (!$data && !$value) {
          return (isset($data) && isset($value)) || (!isset($data) && !isset($value));
        }
        return $data == $value;
    }
  }

}
