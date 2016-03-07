<?php

/**
 * @file
 * Rules controller.
 */

namespace Drupal\rules\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Rules routes.
 */
class RulesController extends ControllerBase {

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
