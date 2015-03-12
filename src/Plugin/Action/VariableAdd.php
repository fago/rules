<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\VariableAdd.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Core\RulesActionBase;

/**
 * @Action(
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
