<?php

/**
 * @file
 * Contains Drupal\rules\TypedData\TypedDataManagerInterface
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Enhanced version of the core typed data manager interface.
 */
interface TypedDataManagerInterface extends \Drupal\Core\TypedData\TypedDataManagerInterface {

  /**
   * Returns a value as specified in the selector.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $typed_data
   *   The data from which to select a value.
   * @param string $selector
   *   The selector string, e.g. "uid:entity:mail:value".
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
  public function applyDataSelector(TypedDataInterface $typed_data, $selector, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED);
}
