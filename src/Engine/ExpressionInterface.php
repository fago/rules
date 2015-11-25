<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExpressionInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Executable\ExecutableInterface;

/**
 * Defines the interface for Rules expressions.
 *
 * @see \Drupal\rules\Engine\ExpressionManager
 */
interface ExpressionInterface extends ExecutableInterface, ConfigurablePluginInterface {

  /**
   * Execute the expression with a given Rules state.
   *
   * Note that this does not auto-save any changes.
   *
   * @param \Drupal\rules\Engine\RulesStateInterface $state
   *   The state with all the execution variables in it.
   *
   * @return null|bool
   *   The expression may return a boolean value after execution, this is used
   *   by conditions that return their evaluation result.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   Thrown if the Rules expression triggers errors during execution.
   */
  public function executeWithState(RulesStateInterface $state);

}
