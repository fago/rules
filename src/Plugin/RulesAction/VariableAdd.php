<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\VariableAdd.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * @RulesAction(
 *   id = "rules_variable_add",
 *   label = @Translation("Add a variable"),
 *   category = @Translation("Variable"),
 *   context = {
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
   * Executes the plugin.
   */
  public function execute() {
    $this->setProvidedValue('variable_added', $this->getContext('value')->getContextValue());
  }

}
