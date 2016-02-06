<?php

/**
 * @file
 * Contains Drupal\rules\Core\RulesUiDefaultHandler.
 */

namespace Drupal\rules\Core;

use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * The default handler for RulesUi plugins.
 *
 * @todo: Complete implementation.
 */
class RulesUiDefaultHandler extends PluginBase implements RulesUiHandlerInterface {

  /**
   * The rules UI (plugin) definition.
   *
   * @var \Drupal\rules\Core\RulesUiDefinition
   */
  protected $pluginDefinition;

  /**
   * {@inheritdoc}
   */
  public function registerRoutes(RouteCollection $collection) {
    $base_route = $collection->get($this->pluginDefinition->base_route);

    $options = [
      'parameters' => ($base_route->getOption('parameters') ?: []),
      '_admin_route' => $base_route->getOption('_admin_route') ?: FALSE,
    ];
    $requirements = [
      '_permission' => $this->pluginDefinition->permissions ?: $base_route->getRequirement('_permission'),
    ];

    $route = (new Route($base_route->getPath() . '/add/{expression_id}'))
      ->addDefaults([
        '_form' => '\Drupal\rules\Form\AddExpressionForm',
        '_title_callback' => '\Drupal\rules\Form\AddExpressionForm::getTitle',
      ])
      ->addOptions($options)
      ->addRequirements($requirements);
    $collection->add($this->pluginDefinition->base_route . '.expression.add', $route);

    $route = (new Route($base_route->getPath() . '/edit/{uuid}'))
      ->addDefaults([
        '_form' => '\Drupal\rules\Form\EditExpressionForm',
        '_title_callback' => '\Drupal\rules\Form\EditExpressionForm::getTitle',
      ])
      ->addOptions($options)
      ->addRequirements($requirements);
    $collection->add($this->pluginDefinition->base_route . '.expression.edit', $route);

    $route = (new Route($base_route->getPath() . '/delete/{uuid}'))
      ->addDefaults([
        '_form' => '\Drupal\rules\Form\DeleteExpressionForm',
        '_title' => 'Delete expression',
      ])
      ->addOptions($options)
      ->addRequirements($requirements);
    $collection->add($this->pluginDefinition->base_route . '.expression.delete', $route);
  }

}
