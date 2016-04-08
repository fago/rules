<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'numeric calculation' action.
 *
 * @RulesAction(
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
 *   provides = {
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
   * Executes the action with the given context.
   *
   * @param float $input_1
   *   The first input value.
   * @param string $operator
   *   The operator that should be applied.
   * @param float $input_2
   *   The second input value.
   */
  protected function doExecute($input_1, $operator, $input_2) {
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
