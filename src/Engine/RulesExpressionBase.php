<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesExpressionBase.
 */

namespace Drupal\rules\Engine;

/**
 * Provides base methods for Rules expression objects.
 */
trait RulesExpressionBase {

  /**
   * Executes a rules expression.
   */
  public function execute() {
    $contexts = $this->getContexts();
    $state = new RulesState($contexts);
    $this->executeWithState($state);
    // Save specifically registered variables in the end after execution.
    $state->autoSave();
  }

}
