<?php

namespace Drupal\rules\Ui;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Form\EmbeddedComponentForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The default handler for RulesUi plugins that store to config.
 *
 * It follows a list of supported settings. Note that settings that are not
 * marked as optional are required.
 * - config_parameter: The name of the routing parameter holding a config
 *   object providing the edited component. The parameter object must implement
 *   \Drupal\rules\Ui\RulesUiComponentProviderInterface. Required, unless
 *   config_name and config_key are provided.
 * - config_name: The name of a (simple) configuration object containing the
 *   configuration data of the edited component. For example,
 *   'your_module.your_config'. Required if 'config_parameter' is omitted.
 * - config_key: The key used to get/set the configuration of the edited
 *   component. For example, 'conditions' or 'foo.conditions'. Required if
 *   'config_parameter' is omitted.
 *
 * @see RulesUiDefinition::settings
 */
class RulesUiConfigHandler extends PluginBase implements RulesUiHandlerInterface, ContainerFactoryPluginInterface {

  use TempStoreTrait;

  /**
   * The rules UI (plugin) definition.
   *
   * @var \Drupal\rules\Ui\RulesUiDefinition
   */
  protected $pluginDefinition;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('current_route_match'), $container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $route_match;
    $this->configFactory = $config_factory;
  }

  /**
   * Gets the edited config object.
   *
   * @return \Drupal\rules\Ui\RulesUiComponentProviderInterface|\Drupal\Core\Config\Config
   *   The component provider object (usually a config entity) or the editable
   *   config object.
   */
  public function getConfig() {
    $config = $this->fetchFromTempStore();
    if (!$config) {
      if (isset($this->pluginDefinition->settings['config_parameter'])) {
        $config = $this->currentRouteMatch->getParameter($this->pluginDefinition->settings['config_parameter']);
      }
      else {
        $config = $this->configFactory->getEditable($this->pluginDefinition->settings['config_name']);
      }
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
    $config = $this->getConfig();
    if ($config instanceof RulesUiComponentProviderInterface) {
      return $config->getComponent();
    }
    else {
      $configuration = $config->get($this->pluginDefinition->settings['config_key']);
      return RulesComponent::createFromConfiguration($configuration);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateComponent(RulesComponent $component) {
    $config = $this->getConfig();
    if ($config instanceof RulesUiComponentProviderInterface) {
      $config->updateFromComponent($component);
    }
    else {
      $config->set($this->pluginDefinition->settings['config_key'], $component->getConfiguration());
    }
    $this->storeToTempStore($config);
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseRouteUrl(array $options = []) {
    // See Url::fromRouteMatch()
    return Url::fromRoute(
      $this->pluginDefinition->base_route,
      $this->currentRouteMatch->getRawParameters()->all(),
      $options
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getUrlFromRoute($route_suffix, array $route_parameters, array $options = []) {
    // See Url::fromRouteMatch()
    return Url::fromRoute(
      $this->pluginDefinition->base_route . '.' . $route_suffix,
      $route_parameters + $this->currentRouteMatch->getRawParameters()->all(),
      $options
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getForm() {
    return new EmbeddedComponentForm($this);
  }

}
