<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesEventManager.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\CategorizingPluginManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\CategorizingPluginManagerTrait;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Core\RulesDefaultEventHandler;
use Drupal\rules\Core\RulesEventHandlerInterface;

/**
 * Plugin manager for Rules events that can be triggered.
 *
 * Rules events are primarily defined in *.rules.events.yml files.
 *
 * @see \Drupal\rules\Core\RulesEventInterface
 */
class RulesEventManager extends DefaultPluginManager implements CategorizingPluginManagerInterface {

  use CategorizingPluginManagerTrait;

  /**
   * Provides some default values for the definition of all Rules event plugins.
   *
   * @var array
   */
  protected $defaults = [
    'class' => RulesDefaultEventHandler::class,
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->alterInfo('rules_event');
    $this->discovery = new ContainerDerivativeDiscoveryDecorator(new YamlDiscovery('rules.events', $module_handler->getModuleDirectories()));
    $this->factory = new ContainerFactory($this, RulesEventHandlerInterface::class);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    if (!isset($definition['context'])) {
      $definition['context'] = [];
    }
    // Convert the flat context arrays into ContextDefinition objects.
    foreach ($definition['context'] as $context_name => $values) {
      $definition['context'][$context_name] = ContextDefinition::createFromArray($values);
    }
  }

}
