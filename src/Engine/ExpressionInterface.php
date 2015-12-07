<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExpressionInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Executable\ExecutableInterface;
use Drupal\rules\Context\ContextProviderInterface;

/**
 * Defines the interface for Rules expressions.
 *
 * @see \Drupal\rules\Engine\ExpressionManager
 */
interface ExpressionInterface extends ExecutableInterface, ContextAwarePluginInterface, ContextProviderInterface, ConfigurablePluginInterface {

  /**
   * Execute the expression with a given Rules state.
   *
   * @param \Drupal\rules\Engine\RulesStateInterface $state
   *   The state with all the execution variables in it.
   *
   * @return null|bool
   *   The expression may return a boolean value after execution, this is used
   *   by conditions that return their evaluation result.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   In case the Rules expression triggers errors during execution.
   */
  public function executeWithState(RulesStateInterface $state);

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
   * Gets the config entity ID this expression is associatedd with.
   *
   * @return string|null
   *   The config entity ID or NULL if the expression is not associated with a
   *   config entity.
   */
  public function getConfigEntityId();

  /**
   * Sets the config entity this expression is associated with.
   *
   * @param string $id
   *   The config entity ID.
   */
  public function setConfigEntityId($id);

  /**
   * The label of this expression element that can be shown in the UI.
   *
   * @return string
   *   The label for display.
   */
  public function getLabel();

}
