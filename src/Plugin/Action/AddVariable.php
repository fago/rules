<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\AddVariable.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Engine\RulesActionBase;

/**
 * @Action(
 *   id = "rules_add_variable",
 *   label = @Translation("Add a variable"),
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
class AddVariable extends RulesActionBase {

  /**
   * Executes the plugin.
   */
  public function execute() {
    $this->setProvidedValue('variable_added', $this->getContext('value')->getContextValue());
  }
}
