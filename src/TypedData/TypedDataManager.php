<?php

/**
 * @file
 * Contains Drupal\rules\TypedData\TypedDataManager
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\rules\Exception\RulesEvaluationException;

/**
 * Enhanced version of the core typed data manager.
 */
class TypedDataManager extends \Drupal\Core\TypedData\TypedDataManager implements TypedDataManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function applyDataSelector(TypedDataInterface $typed_data, $selector, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    $parts = explode(':', $selector, 2);

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
          throw new RulesEvaluationException("Unable to apply data selector $current_selector. The specified reference is NULL.");
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
          throw new RulesEvaluationException("Unable to apply data selector $current_selector: " . $e->getMessage());
        }
      }
      else {
        throw new RulesEvaluationException("Unable to apply data selector $current_selector. The specified variable is not a list or a complex structure: $name.");
      }
    }

    return $typed_data;
  }
}
