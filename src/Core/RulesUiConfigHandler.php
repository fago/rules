<?php

/**
 * @file
 * Contains Drupal\rules\Core\RulesUiDefaultHandler.
 */

namespace Drupal\rules\Core;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Form\TempStoreTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The default handler for RulesUi plugins that store to config.
 *
 * It follows a list of supported settings. Note that settings that are not
 * marked as optional are required.
 * - config_parameter: The name of the routing parameter holding the config.
 *
 * @see RulesUiDefinition::settings
 */
class RulesUiConfigHandler extends PluginBase implements RulesUiHandlerInterface, ContainerFactoryPluginInterface {

  use TempStoreTrait;

  /**
   * The rules UI (plugin) definition.
   *
   * @var \Drupal\rules\Core\RulesUiDefinition
   */
  protected $pluginDefinition;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('current_route_match'));
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $route_match;
  }

  /**
   * Gets the edited config object.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityBase|\Drupal\Core\Config\Config
   *   The config entity or config object.
   */
  public function getConfig() {
    $config = $this->fetchFromTempStore();
    if (!$config) {
      $config = $this->currentRouteMatch->getParameter($this->pluginDefinition->settings['config_parameter']);
    }
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentLabel() {
    if (isset($this->pluginDefinition->component_label)) {
      return $this->pluginDefinition->component_label;
    }
    elseif ($this->getConfig() instanceof EntityInterface) {
      return $this->getConfig()->label();
    }
    else {
      return $this->pluginDefinition->component_type_label;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getComponent() {
    return $this->getConfig()->getComponent();
  }

  /**
   * {@inheritdoc}
   */
  public function updateComponent(RulesComponent $component) {
    $config = $this->getConfig();
    $config->updateFromComponent($component);
    $this->storeToTempStore($config);
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseRouteUrl() {
    // See Url::fromRouteMatch()
    return Url::fromRoute($this->pluginDefinition->base_route, $this->currentRouteMatch->getRawParameters()->all());
  }

}
