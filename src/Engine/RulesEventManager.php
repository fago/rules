<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesEventManager.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Plugin manager for Rules events that can be triggered.
 *
 * Rules events are primarily defined in *.rules.events.yml files.
 *
 * @see \Drupal\rules\Core\RulesEventInterface
 */
class RulesEventManager extends DefaultPluginManager {

  use StringTranslationTrait;

  /**
   * Provides some default values for the definition of all Rules event plugins.
   *
   * @var array
   */
  protected $defaults = [
    'class' => '\Drupal\rules\Core\RulesEvent',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->alterInfo('rules_event');
    $this->discovery = new YamlDiscovery('rules.events', $module_handler->getModuleDirectories());
    $this->factory = new ContainerFactory($this, 'Drupal\rules\Core\RulesEventInterface');
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    if (!isset($definition['context'])) {
      return;
    }
    // Convert the flat context arrays into ContextDefinition objects.
    // @todo This code should be removed and we should pass this off to some
    //   annotation reader code that converts plugin defintion parts into
    //   objects.
    foreach ($definition['context'] as $context_name => $values) {
      $values += array(
        'required' => TRUE,
        'multiple' => FALSE,
        'default_value' => NULL,
      );
      foreach (['label', 'description'] as $key) {
        if (isset($values[$key])) {
          // @todo Dynamic translations are bad! But how can specify
          //   translatable strings in the plugin definition YAML file?
          $values[$key] = $this->t($values[$key]);
        }
        else {
          $values[$key] = NULL;
        }
      }
      if (isset($values['class']) && !in_array('Drupal\Core\Plugin\Context\ContextDefinitionInterface', class_implements($values['class']))) {
        throw new PluginException('ContextDefinition class must implement \Drupal\Core\Plugin\Context\ContextDefinitionInterface.');
      }
      $class = isset($values['class']) ? $values['class'] : 'Drupal\Core\Plugin\Context\ContextDefinition';
      $definition['context'][$context_name] = new $class(
        $values['type'], $values['label'], $values['required'],
        $values['multiple'], $values['description'], $values['default_value']);
    }
  }

}
