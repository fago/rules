<?php

namespace Drupal\rules\Core\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Rules Action annotation object.
 *
 * Plugin Namespace: Plugin\RulesAction.
 *
 * For a working example, see \Drupal\rules\Plugin\RulesAction\BanIP
 *
 * @see \Drupal\rules\Core\RulesActionInterface
 * @see \Drupal\rules\Core\RulesActionManagerInterface
 * @see \Drupal\rules\Core\RulesActionBase
 * @see plugin_api
 *
 * @Annotation
 */
class RulesAction extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the action plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The category under which the action should be listed in the UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $category;

  /**
   * The permission required to access the configuration UI for this plugin.
   *
   * @var string[]
   *   Array of permission string as declared in a *.permissions.yml file.
   */
  public $configure_permission;

  /**
   * Defines the used context of the action plugin.
   *
   * Array keys are the names of the contexts and values context definitions.
   *
   * @var \Drupal\Core\Annotation\ContextDefinition[]
   */
  public $context = [];

  /**
   * Defines the provided context of the action plugin.
   *
   * Array keys are the names of the contexts and values context definitions.
   *
   * @var \Drupal\Core\Annotation\ContextDefinition[]
   */
  public $provides = [];

}
