<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides an 'Add a variable' action.
 *
 * @RulesAction(
 *   id = "rules_variable_add",
 *   label = @Translation("Add a variable"),
 *   category = @Translation("Variable"),
 *   context = {
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Type"),
 *       assignment_restriction = "input"
 *     ),
 *     "value" = @ContextDefinition("any",
 *       label = @Translation("Value")
 *     ),
 *   },
 *   provides = {
 *     "variable_added" = @ContextDefinition("any",
 *        label = @Translation("Added variable")
 *      )
 *    }
 * )
 */
class VariableAdd extends RulesActionBase {

  /**
   * Add a variable.
   *
   * @param string $type
   *   The data type the new variable is of.
   * @param mixed $value
   *   The variable to add.
   */
  protected function doExecute($type, $value) {
    $this->setProvidedValue('variable_added', $value);
  }

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions(array $selected_data) {
    if ($type = $this->getContextValue('type')) {
      $this->pluginDefinition['context']['value']->setDataType($type);
      $this->pluginDefinition['provides']['variable_added']->setDataType($type);
    }
  }

}
