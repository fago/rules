<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesState.
 */

namespace Drupal\rules\Engine;

use Drupal\rules\Context\Context;

/**
 * The rules evaluation state.
 *
 * A rule element may clone the state, so any added variables are only visible
 * for elements in the current PHP-variable-scope.
 */
class RulesState {

  /**
   * Globally keeps the ids of rules blocked due to recursion prevention.
   *
   * @todo Implement recursion prevention from D7.
   */
  static protected $blocked = [];

  /**
   * The known variables.
   *
   * @var \Drupal\rules\Context\Context[]
   */
  protected $variables = [];

  /**
   * Variable for saving currently blocked configs for serialization.
   */
  protected $currentlyBlocked;

  /**
   * Creates a new RulesState object.
   *
   * @param \Drupal\rules\Context\Context[] $contexts
   *   Context variables to initialize this state with (optional).
   */
  public function __construct($contexts = []) {
    $this->variables = $contexts;
    // @todo Initialize the gloabl "site" variable.
  }

  /**
   * Adds the given variable to the given execution state.
   *
   * @param string $name
   *   The varible name.
   * @param \Drupal\rules\Context\Context $context
   *   The variable wrapped as context.
   */
  public function addVariable($name, Context $context) {
    $this->variables[$name] = $context;
  }

  /**
   * Gets a variable.
   *
   * @param string $name
   *   The name of the variable to return.
   *
   * @return \Drupal\rules\Context\Context
   *   The variable wrapped as context.
   *
   * @throws RulesEvaluationException
   *   Throws a RulesEvaluationException if the variable does not exist in the
   *   state.
   */
  public function getVariable($name) {
    if (!array_key_exists($name, $this->variables)) {
      throw new RulesEvaluationException("Unable to get variable $name, it is not defined.");
    }
    return $this->variables[$name];
  }

}
