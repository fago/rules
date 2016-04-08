<?php

namespace Drupal\rules\Ui;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;

/**
 * Plugin manager for Rules Ui instances.
 *
 * Rules UIs are primarily defined in *.rules_ui.yml files. Usually, there is
 * no need to specify a 'class' as there is a suiting default handler class in
 * place. However, if done see the class must implement
 * \Drupal\rules\Ui\RulesUiHandlerInterface.
 *
 * @see \Drupal\rules\Ui\RulesUiHandlerInterface
 */
class RulesUiManager extends DefaultPluginManager implements RulesUiManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->alterInfo('rules_ui');
    $this->discovery = new ContainerDerivativeDiscoveryDecorator(new YamlDiscovery('rules_ui', $module_handler->getModuleDirectories()));
    $this->factory = new ContainerFactory($this, RulesUiHandlerInterface::class);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    $definition = new RulesUiDefinition($definition);
    $definition->validate();
  }

}
