<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides a 'Text comparison' condition.
 *
 * @Condition(
 *   id = "rules_text_comparison",
 *   label = @Translation("Text comparison"),
 *   category = @Translation("Data"),
 *   context = {
 *     "text" = @ContextDefinition("string",
 *       label = @Translation("Text")
 *     ),
 *     "operator" = @ContextDefinition("string",
 *       label = @Translation("Operator"),
 *       description = @Translation("The comparison operator. One of 'contains', 'starts', 'ends', or 'regex'. Defaults to 'contains'."),
 *       default_value = "contains",
 *       required = FALSE
 *     ),
 *     "match" = @ContextDefinition("string",
 *        label = @Translation("Matching text")
 *     )
 *   }
 * )
 */
class TextComparison extends RulesConditionBase {

  /**
   * Evaluate the text comparison.
   *
   * @param string $text
   *   The supplied text string.
   * @param string $operator
   *   Text comparison operator. One of:
   *   - contains: (default) Evaluate if $text contains $match.
   *   - starts: Evaluate if $text starts with $match.
   *   - ends: Evaluate if $text ends with $match.
   *   - regex: Evaluate if a regular expression in $match matches $text.
   *   Values that do not match one of these operators default to "contains".
   * @param string $match
   *   The string to be compared against $text.
   *
   * @return bool
   *   The evaluation of the condition.
   */
  protected function doEvaluate($text, $operator, $match) {
    $operator = $operator ? $operator : 'contains';
    switch ($operator) {
      case 'starts':
        return strpos($text, $match) === 0;

      case 'ends':
        return strrpos($text, $match) === (strlen($text) - strlen($match));

      case 'regex':
        return (bool) preg_match('/' . str_replace('/', '\\/', $match) . '/', $text);

      case 'contains':
      default:
        // Default operator "contains".
        return strpos($text, $match) !== FALSE;
    }
  }

}
