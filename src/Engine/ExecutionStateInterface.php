<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExecutionStateInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\rules\Context\ContextDefinitionInterface;

/**
 * Defines an interface for the rules state.
 */
interface ExecutionStateInterface {

  /**
   * Adds a state variable based on its definition and value.
   *
   * @param string $name
   *   The context variable name.
   * @param \Drupal\rules\Context\ContextDefinitionInterface $definition
   *   The context definition of the variable.
   * @param mixed $value
   *   The variable value.
   *
   * @return $this
   */
  public function addVariable($name, ContextDefinitionInterface $definition, $value);

  /**
   * Adds a state variable from some typed data object.
   *
   * @param string $name
   *   The variable name.
   * @param \Drupal\Core\TypedData\TypedDataInterface $data
   *   The variable wrapped as typed data.
   *
   * @return $this
   */
  public function addVariableData($name, TypedDataInterface $data);

  /**
   * Gets a variable.
   *
   * @param string $name
   *   The name of the variable to return.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The variable wrapped as typed data.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   Throws a RulesEvaluationException if the variable does not exist in the
   *   state.
   */
  public function getVariable($name);

  /**
   * Gets the value of a variable.
   *
   * @param string $name
   *   The name of the variable to return the value for.
   *
   * @return mixed
   *   The variable value.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   Throws a RulesEvaluationException if the variable does not exist in the
   *   state.
   */
  public function getVariableValue($name);

  /**
   * Checks if a variable exists by name in the Rules state.
   *
   * @param string $name
   *   The variable name.
   *
   * @return bool
   *   TRUE if the variable exists, FALSE otherwise.
   */
  public function hasVariable($name);

  /**
   * Returns a value as specified in the selector.
   *
   * @param string $selector
   *   The selector string, e.g. "node:uid:entity:mail:value".
   * @param string $langcode
   *   (optional) The language code used to get the argument value if the
   *   argument value should be translated. Defaults to
   *   LanguageInterface::LANGCODE_NOT_SPECIFIED.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The variable wrapped as typed data.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   Throws a RulesEvaluationException in case the selector cannot be applied.
   */
  public function applyDataSelector($selector, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED);

  /**
   * Mark a variable to be saved later when the execution is finished.
   *
   * @param string $selector
   *   The data selector that specifies the target object to be saved. Example:
   *   node:uid:entity.
   *
   * @return $this
   */
  public function saveChangesLater($selector);

  /**
   * Saves all variables that have been marked for auto saving.
   *
   * @return $this
   */
  public function autoSave();

}
