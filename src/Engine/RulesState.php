<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesState.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\rules\Exception\RulesEvaluationException;

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
   * @var \Drupal\Core\Plugin\Context\ContextInterface[]
   */
  protected $variables = [];

  /**
   * Holds variables for auto-saving later.
   *
   * @var array
   */
  protected $saveLater = [];

  /**
   * Variable for saving currently blocked configs for serialization.
   */
  protected $currentlyBlocked;

  /**
   * Creates a new RulesState object.
   *
   * @param \Drupal\Core\Plugin\Context\ContextInterface[] $contexts
   *   Context variables to initialize this state with (optional).
   */
  public function __construct($contexts = []) {
    $this->variables = $contexts;
    // @todo Initialize the global "site" variable.
  }

  /**
   * Adds the given variable to the given execution state.
   *
   * @param string $name
   *   The varible name.
   * @param \Drupal\Core\Plugin\Context\ContextInterface $context
   *   The variable wrapped as context.
   */
  public function addVariable($name, ContextInterface $context) {
    $this->variables[$name] = $context;
  }

  /**
   * Gets a variable.
   *
   * @param string $name
   *   The name of the variable to return.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface
   *   The variable wrapped as context.
   *
   * @throws RulesEvaluationException
   *   Throws a RulesEvaluationException if the variable does not exist in the
   *   state.
   */
  public function getVariable($name) {
    if (!array_key_exists($name, $this->variables)) {
      throw new RulesEvaluationException(SafeMarkup::format('Unable to get variable @name, it is not defined.', [
        '@name' => $name,
      ]));
    }
    return $this->variables[$name];
  }

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
   * @throws RulesEvaluationException
   *   Throws a RulesEvaluationException in case the selector cannot be applied.
   */
  public function applyDataSelector($selector, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    $parts = explode(':', $selector, 2);
    $context = $this->getVariable($parts[0]);
    $typed_data = $context->getContextData();
    if (count($parts) == 1) {
      return $typed_data;
    }
    $current_selector = $parts[0];
    foreach (explode(':', $parts[1]) as $name) {
      // If the current data is just a reference then directly dereference the
      // target.
      if ($typed_data instanceof DataReferenceInterface) {
        $typed_data = $typed_data->getTarget();
        if ($typed_data === NULL) {
          throw new RulesEvaluationException(SafeMarkup::format('Unable to apply data selector @current_selector. The specified reference is NULL.', [
            '@current_selector' => $current_selector,
          ]));
        }
      }

      // Make sure we are using the right language.
      if ($typed_data instanceof TranslatableInterface) {
        if ($typed_data->hasTranslation($langcode)) {
          $typed_data = $typed_data->getTranslation($langcode);
        }
        // @todo What if the requested translation does not exist? Currently
        // we just ignore that and continue with the current object.
      }

      // If this is a list but the selector is not an integer, we forward the
      // selection to the first element in the list.
      if ($typed_data instanceof ListInterface && !ctype_digit($name)) {
        $typed_data = $typed_data->offsetGet(0);
      }

      $current_selector .= ":$name";

      // Drill down to the next step in the data selector.
      if ($typed_data instanceof ListInterface || $typed_data instanceof ComplexDataInterface) {
        try {
          $typed_data = $typed_data->get($name);
        }
        catch (\InvalidArgumentException $e) {
          // In case of an exception, re-throw it.
          throw new RulesEvaluationException(SafeMarkup::format('Unable to apply data selector @current_selector: @error', [
            '@current_selector' => $current_selector,
            '@error' => $e->getMessage(),
          ]));
        }
      }
      else {
        throw new RulesEvaluationException(SafeMarkup::format('Unable to apply data selector @current_selector. The specified variable is not a list or a complex structure: @name.', [
          '@current_selector' => $current_selector,
          '@name' => $name,
        ]));
      }
    }

    return $typed_data;
  }

  /**
   * Mark a variable to be saved later when the execution is finished.
   *
   * @param string $selector
   *   The data selector that specifies the target object to be saved. Example:
   *   node:uid:entity.
   */
  public function saveChangesLater($selector) {
    $this->saveLater[$selector] = TRUE;
  }

  /**
   * Saves all variables that have been marked for auto saving.
   */
  public function autoSave() {
    // Make changes permanent.
    foreach ($this->saveLater as $selector => $flag) {
      $typed_data = $this->applyDataSelector($selector);
      // The returned data can be NULL, only save it if we actually have
      // something here.
      if ($typed_data) {
        // Things that can be saved must have a save() method, right?
        $typed_data->getValue()->save();
      }
    }
  }

}
