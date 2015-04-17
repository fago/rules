<?php

/**
 * @file
 * Stub that returns 'not implemented' message for RulesReactions routings.
 */

namespace Drupal\rules\Stub;

/**
 * Provides route responses.
 */
class RulesReactionStub {
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function notImplemented() {
    $element = array(
      '#markup' => 'Not implemented yet.',
    );
    return $element;
  }
}
