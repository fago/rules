<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesExpressionTrait.
 */

namespace Drupal\rules\Engine;

/**
 * Provides base methods for Rules expression objects.
 */
trait RulesExpressionTrait {

  /**
   * @var \Drupal\rules\Plugin\RulesExpressionPluginManager
   */
  protected $expressionManager;

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
