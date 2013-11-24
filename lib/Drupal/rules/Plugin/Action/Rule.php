<?php

/**
 * @file
 * Contains
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;

/**
 * Container for consitions and actions.
 *
 * @Action(
 *   id = "rules_rule",
 *   label = @Translation("Execute a rule"),
 *   type = "rules"
 * )
 */
class Rule extends ConfigurableActionBase {
  public function buildConfigurationForm(array $form, array &$form_state) {

  }

  public function execute() {
    // Evaluate conditions, if they pass execute actions.
  }

  public function submitConfigurationForm(array &$form, array &$form_state) {

  }

}
