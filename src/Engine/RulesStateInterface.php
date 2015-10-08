<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesStateInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Defines an interface for the rules state.
 */
interface RulesStateInterface {

  /**
   * Adds the given variable to the given execution state.
   *
   * @param string $name
   *   The variable name.
   * @param \Drupal\Core\TypedData\TypedDataInterface $data
   *   The variable wrapped as typed data.
   */
  public function addVariable($name, TypedDataInterface $data);

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
   */
  public function saveChangesLater($selector);

  /**
   * Saves all variables that have been marked for auto saving.
   */
  public function autoSave();

}
