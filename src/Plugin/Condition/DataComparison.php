<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\DataComparison.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'Data comparison' condition.
 *
 * @Condition(
 *   id = "rules_data_is",
 *   label = @Translation("Data comparison"),
 *   context = {
 *     "data" = @ContextDefinition("any",
 *       label = @Translation("Data to compare"),
 *       description = @Translation("The data to be checked to be empty, specified by using a data selector, e.g. 'node:uid:entity:name:value'.")
 *     ),
 *     "op" = @ContextDefinition("string",
 *       label = @Translation("Operator"),
 *       description = @Translation("The comparison operator."),
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
 * @todo: Add group information from Drupal 7.
 * @todo: Find a way to port rules_condition_data_is_operator_options() from Drupal 7.
 */
class DataComparison extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Data comparison');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $data = $this->getContextValue('data');
    $op = $this->getContext('op')->getContextData() ? $this->getContextValue('op') : '==';
    $value = $this->getContextValue('value');
    
    switch ($op) {
      case '<':
        return $this->isLessThan($data, $value);

      case '>':
        return $this->isGreaterThan($data, $value);

      case 'contains':
        return $this->contains($data, $value);

      case 'IN':
        return $this->isOneOf($data, $value);

      case '==':
      default:
        return $this->equals($data, $value);
    }
  }

  /**
   * Evaluates the "isLessThan" condition.
   */
  protected function isLessThan($data, $value) {
    return $data < $value;
  }

  /**
   * Evaluates the "isGreaterThan" condition.
   */
  protected function isGreaterThan($data, $value) {
    return $data > $value;
  }

  /**
   * Evaluates the "contains" condition.
   *
   * Note: This is deprecated by the text comparison condition and IN below.
   */
  protected function contains($data, $value) {
    return is_string($data) && strpos($data, $value) !== FALSE || is_array($data) && in_array($value, $data);
  }

  /**
   * Evaluates the "IN" condition.
   */
  protected function isOneOf($data, $value) {
    return is_array($value) && in_array($data, $value);
  }

  /**
   * Evaluates the "equals" condition.
   */
  protected function equals($data, $value) {
    // In case both values evaluate to FALSE, further differentiate between
    // NULL values and values evaluating to FALSE.
    if (!$data && !$value) {
      return (isset($data) && isset($value)) || (!isset($data) && !isset($value));
    }
    return $data == $value;
  }

}
