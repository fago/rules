<?php

namespace Drupal\rules\Ui;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Interface for the 'rules_ui' plugin manager.
 *
 * RulesUI plugins allow the definition of multiple RulesUIs instances, possibly
 * being included in some other UI.
 */
interface RulesUiManagerInterface extends PluginManagerInterface {

  /**
   * Creates a pre-configured instance of a plugin.
   *
   * @param string $plugin_id
   *   The ID of the plugin being instantiated.
   * @param array $configuration
   *   An array of configuration relevant to the plugin instance.
   *
   * @return \Drupal\rules\Ui\RulesUiHandlerInterface
   *   A fully configured plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the instance cannot be created, such as if the ID is invalid.
   */
  public function createInstance($plugin_id, array $configuration = []);

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\rules\Ui\RulesUiDefinition|null
   *   A plugin definition, or NULL if the plugin ID is invalid and
   *   $exception_on_invalid is FALSE.
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE);

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\rules\Ui\RulesUiDefinition[]
   *   An array of plugin definitions (empty array if no definitions were
   *   found). Keys are plugin IDs.
   */
  public function getDefinitions();

}
