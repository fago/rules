<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesUiHandlerInterface.
 */

namespace Drupal\rules\Core;

use Symfony\Component\Routing\RouteCollection;

/**
 * Interface for Rules UI handlers.
 *
 * Rules UI handlers define where RulesUI instances are embedded and are
 * responsible for generating the appropriate routes.
 *
 * @todo: Implement.
 */
interface RulesUiHandlerInterface {

  /**
   * Registers the routes as need for the UI.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection to which to add the routes.
   */
  public function registerRoutes(RouteCollection $collection);

}
