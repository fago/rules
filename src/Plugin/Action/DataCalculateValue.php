<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\DataCalculateValue.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'numeric calculation' action.
 *
 * @Action(
 *   id = "rules_data_calculate_value",
 *   label = @Translation("Calculates a numeric value"),
 *   category = @Translation("Data"),
 *   context = {
 *     "input_1" = @ContextDefinition("float",
 *       label = @Translation("Input value 1"),
 *       description = @Translation("The first input value for the calculation.")
 *     ),
 *     "operator" = @ContextDefinition("string",
 *       label = @Translation("Operator"),
 *       description = @Translation("The calculation operator.")
 *     ),
 *     "input_2" = @ContextDefinition("float",
 *       label = @Translation("Input value 2"),
 *       description = @Translation("The second input value for the calculation.")
 *     )
 *   },
 *  provides = {
 *     "result" = @ContextDefinition("float",
 *       label = @Translation("Calculated result")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add defined operation options from Drupal 7.
 * @todo: If context args are integers, ensure that integers are returned.
 */
class DataCalculateValue extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Calculate a numeric value');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $input_1 = $this->getContextValue('input_1');
    $operator = $this->getContextValue('operator');
    $input_2 = $this->getContextValue('input_2');

    switch ($operator) {
      case '+':
        $result = $input_1 + $input_2;
        break;

      case '-':
        $result = $input_1 - $input_2;
        break;

      case '*':
        $result = $input_1 * $input_2;
        break;

      case '/':
        $result = $input_1 / $input_2;
        break;

      case 'min':
        $result = min($input_1, $input_2);
        break;

      case 'max':
        $result = max($input_1, $input_2);
        break;
    }

    if (isset($result)) {
      $this->setProvidedValue('result', $result);
    }
  }

}
