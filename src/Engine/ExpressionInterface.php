<?php

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
   * @throws \Drupal\rules\Exception\EvaluationException
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
   * Verifies that this expression is configured correctly.
   *
   * Example: All configured data selectors must be valid.
   *
   * Note that for checking integrity the execution metadata state must be
   * passed prepared as achieved by ::prepareExecutionMetadataState() and the
   * expression must apply all metadata state preparations during its integrity
   * check as it does in ::prepareExecutionMetadataState().
   * This allows for efficient integrity checks of expression trees; e.g. see
   * \Drupal\rules\Engine\ActionExpressionContainer::checkIntegrity().
   *
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The execution metadata state, prepared until right before this
   *   expression.
   * @param bool $apply_assertions
   *   (optional) Whether to apply metadata assertions while preparing the
   *   execution metadata state. Defaults to TRUE.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationList
   *   A list object containing \Drupal\rules\Engine\IntegrityViolation objects.
   *
   * @see ::prepareExecutionMetadataState()
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state, $apply_assertions = TRUE);

  /**
   * Prepares the execution metadata state by adding metadata to it.
   *
   * If this expression contains other expressions then the metadata state is
   * set up recursively. If a $until expression is specified then the setup will
   * stop right before that expression to calculate the state at this execution
   * point.
   * This is useful for inspecting the state at a certain point in the
   * expression tree as needed during configuration, for example to do
   * autocompletion of available variables in the state.
   *
   * The difference to fully preparing the state is that not necessarily all
   * variables are available in the middle of the expression tree, as for
   * example variables being added later are not added yet. Preparing with
   * $until = NULL reflects the execution metadata state at the end of the
   * expression execution.
   *
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The execution metadata state, prepared until right before this
   *   expression.
   * @param \Drupal\rules\Engine\ExpressionInterface $until
   *   (optional) The expression at which metadata preparation should be
   *   stopped. The preparation of the state will be stopped right before that
   *   expression.
   * @param bool $apply_assertions
   *   (optional) Whether to apply metadata assertions while preparing the
   *   execution metadata state. Defaults to TRUE. Metadata assertions should
   *   be only applied if the expression's execution is required for sub-sequent
   *   expressions being executed. For example, if a condition is optional as
   *   it is part of a logical OR expression, its assertions may not be applied.
   *   Defaults to TRUE.
   *
   * @return true|null
   *   True if the metadata has been prepared and the $until expression was
   *   found in the tree. Null otherwise.
   */
  public function prepareExecutionMetadataState(ExecutionMetadataStateInterface $metadata_state, ExpressionInterface $until = NULL, $apply_assertions = TRUE);

}
