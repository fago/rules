<?php

/**
 * @file
 * Contains \Drupal\rules\Routing\RulesUiRouteEnhancer.
 */

namespace Drupal\rules\Routing;

use Drupal\Core\Routing\Enhancer\RouteEnhancerInterface;
use Drupal\rules\Ui\RulesUiManagerInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Enhances routes with the specified RulesUI.
 *
 * Routes have the plugin ID of the active RulesUI instance set on the _rules_ui
 * option. Based upon that information, this enhances adds the following
 * parameters to the routes:
 * - rules_ui_handler: The RulesUI handler, as specified by the plugin.
 * - rules_component: The rules component being edited, as provided by the
 *   handler.
 */
class RulesUiRouteEnhancer implements RouteEnhancerInterface {

  /**
   * The rules_ui plugin manager.
   *
   * @var \Drupal\rules\Ui\RulesUiManagerInterface
   */
  protected $rulesUiManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\rules\Ui\RulesUiManagerInterface $rules_ui_manager
   *   The rules_ui plugin manager.
   */
  public function __construct(RulesUiManagerInterface $rules_ui_manager) {
    $this->rulesUiManager = $rules_ui_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    // @var $route \Symfony\Component\Routing\Route
    $route = $defaults[RouteObjectInterface::ROUTE_OBJECT];
    $plugin_id = $route->getOption('_rules_ui');
    $defaults['rules_ui_handler'] = $this->rulesUiManager->createInstance($plugin_id);
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return ($route->hasOption('_rules_ui'));
  }

}
