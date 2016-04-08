<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Delete any path alias' action.
 *
 * @RulesAction(
 *   id = "rules_path_alias_delete_by_alias",
 *   label = @Translation("Delete path alias"),
 *   category = @Translation("Path"),
 *   context = {
 *     "alias" = @ContextDefinition("string",
 *       label = @Translation("Existing system path alias"),
 *       description = @Translation("Specifies the existing path alias you wish to delete, for example 'about/team'. Use a relative path and do not add a trailing slash.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class PathAliasDeleteByAlias extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The alias storage service.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * Constructs a PathAliasDeleteByAlias object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\AliasStorageInterface $alias_storage
   *   The alias storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AliasStorageInterface $alias_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->aliasStorage = $alias_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.alias_storage')
    );
  }

  /**
   * Delete an existing alias.
   *
   * @param string $alias
   *   Alias to be deleted.
   */
  protected function doExecute($alias) {
    $this->aliasStorage->delete(['alias' => $alias]);
  }

}
