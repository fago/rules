<?php

namespace Drupal\rules\Ui;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\rules\Exception\LogicException;

/**
 * Class for rules_ui plugin definitions.
 *
 * Note that the class is treated as value object. Thus, there is no special
 * interface for it.
 *
 * @see \Drupal\rules\Ui\RulesUiManagerInterface
 */
class RulesUiDefinition implements PluginDefinitionInterface {

  /**
   * Constructs the object.
   *
   * @param array $values
   *   (optional) Array of initial property values to set.
   */
  public function __construct(array $values = []) {
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var string
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The rules UI handler class.
   *
   * The class must implement \Drupal\rules\Ui\RulesUiHandlerInterface.
   *
   * @var string
   */
  public $class = RulesUiConfigHandler::class;

  /**
   * Array of handler-specific settings.
   *
   * Check the documentation of the ui handler for further details.
   *
   * @var array
   */
  public $settings = [];

  /**
   * The plugin provider; e.g., the module.
   *
   * @var string
   */
  public $provider;

  /**
   * The name of the base route.
   *
   * Generated routes are added below this route.
   *
   * @var string
   */
  public $base_route;

  /**
   * The permission string to use for the generated routes.
   *
   * If omitted, the permission string is inherited from the base route.
   *
   * @var string|null
   */
  public $permissions;

  /**
   * The label used for referring to the component (optional).
   *
   * If omitted, a handler-specific fallback logic is applied. For example,
   * the RulesUiConfigHandler assumes a config entity and uses its label()
   * method.
   *
   * @var string|null
   *
   * @ingroup plugin_translatable
   */
  public $component_label;

  /**
   * The label used for referring to the component type.
   *
   * @var string|null
   *
   * @ingroup plugin_translatable
   */
  public $component_type_label = 'component';

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getProvider() {
    return $this->provider;
  }

  /**
   * {@inheritdoc}
   */
  public function setClass($class) {
    $this->class = $class;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getClass() {
    return $this->class;
  }

  /**
   * Validates the set property values.
   *
   * @throws \Drupal\rules\Exception\LogicException
   *   Thrown if the set object properties are not valid.
   */
  public function validate() {
    if (!isset($this->id)) {
      throw new LogicException("Missing the required property 'id'.");
    }
    foreach (['label', 'class', 'provider', 'base_route'] as $required) {
      if (!isset($this->$required)) {
        throw new LogicException("Plugin {$this->id} misses the required property $required.");
      }
    }
    if (!is_subclass_of($this->class, RulesUiHandlerInterface::class)) {
      throw new LogicException("The provided class does not implement the RulesUiHandlerInterface.");
    }
  }

}
