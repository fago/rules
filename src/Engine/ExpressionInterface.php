<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExpressionInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Executable\ExecutableInterface;

/**
 * Defines the interface for Rules expressions.
 *
 * @see \Drupal\rules\Engine\ExpressionManager
 */
interface ExpressionInterface extends ExecutableInterface, ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Execute the expression with a given Rules state.
   *
   * Note that this does not auto-save any changes.
   *
   * @param \Drupal\rules\Engine\ExecutionStateInterface $state
   *   The state with all the execution variables in it.
   *
   * @return null|bool
   *   The expression may return a boolean value after execution, this is used
   *   by conditions that return their evaluation result.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   Thrown if the Rules expression triggers errors during execution.
   */
  public function executeWithState(ExecutionStateInterface $state);

  /**
   * Returns the form handling class for this expression.
   *
   * @return \Drupal\rules\Form\Expression\ExpressionFormInterface|null
   *   The form handling object if there is one, NULL otherwise.
   */
  public function getFormHandler();

  /**
   * Returns the root expression if this expression is nested.
   *
   * @return \Drupal\rules\Engine\ExpressionInterface
   *   The root expression or $this if the expression is the root element
   *   itself.
   */
  public function getRoot();

  /**
   * Set the root expression for this expression if it is nested.
   *
   * @param \Drupal\rules\Engine\ExpressionInterface $root
   *   The root expression object.
   */
  public function setRoot(ExpressionInterface $root);

  /**
   * The label of this expression element that can be shown in the UI.
   *
   * @return string
   *   The label for display.
   */
  public function getLabel();

  /**
   * Verifies that this expression is configured correctly.
   *
   * Example: all variable names used in the expression are available.
   *
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The configuration state used to hold available data definitions of
   *   variables.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationList
   *   A list object containing \Drupal\rules\Engine\IntegrityViolation objects.
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state);

  /**
   * Returns the UUID of this expression if it is nested in another expression.
   *
   * @return string|null
   *   The UUID if this expression is nested or NULL if it does not have a UUID.
   */
  public function getUuid();

  /**
   * Sets the UUID of this expression in an expression tree.
   *
   * @param string $uuid
   *   The UUID to set.
   */
  public function setUuid($uuid);

  /**
   * Prepares the execution metadata state by adding variables to it.
   *
   * If this expression contains other expressions then the metadata state is
   * set up recursively. If a $until expression is specified then the setup will
   * stop right before that expression. This is useful for inspecting the state
   * at a certain point in the expression tree, for example to do autocompletion
   * of available variables in the state.
   *
   * The difference to fully preparing the state is that not all variables are
   * available in the middle of the expression tree. Preparing with
   * $until = NULL reflects the execution metadata state at the end of the
   * expression.
   *
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The execution metadata state to populate variables in.
   * @param \Drupal\rules\Engine\ExpressionInterface $until
   *   (optional) A nested expression if this expression is a container.
   *   Preparation of the sate will happen right before that expression.
   *
   * @return bool
   *   TRUE if $until is NULL or the nested expression was found in the tree,
   *   FALSE otherwise.
   */
  public function prepareExecutionMetadataState(ExecutionMetadataStateInterface $metadata_state, ExpressionInterface $until = NULL);

}
