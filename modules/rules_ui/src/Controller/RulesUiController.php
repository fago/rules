<?php

/**
 * @file
 * Rules UI controller.
 */

namespace Drupal\rules_ui\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Rules UI routes.
 */
class RulesUiController extends ControllerBase {
  /**
   * Returns the settings page.
   *
   * @return array
   *   Renderable array.
   */
  public function settingsForm() {
    $element = [
      '#markup' => 'Rules settings form is not implemented yet.',
    ];
    return $element;
  }
}
