<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\CreateNodePathAlias.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Engine\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Create node path alias' action.
 *
 * @Action(
 *   id = "rules_node_path_alias_create",
 *   label = @Translation("Create node path alias"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node",
 *       label = @Translation("Content"),
 *       description = @Translation("The content for which to create a path alias.")
 *     ),
 *     "alias" = @ContextDefinition("string",
 *       label = @Translation("Path alias"),
 *       description = @Translation("Specify an alternative path by which the content can be accessed. For example, 'about' for an about page. Use a relative path and do not add a trailing slash.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class CreateNodePathAlias extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The alias storage service.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * Constructs a CreateNodePathAlias object.
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
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Create node path alias');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $alias = $this->getContextValue('alias');
    $node = $this->getContextValue('node');

    // We need to save the node before we can get its internal path.
    if ($node->isNew()) {
      $node->save();
    }

    $path = $node->urlInfo()->getInternalPath();
    $langcode = $node->language()->getId();
    $this->aliasStorage->save($path, $alias, $langcode);
  }

}
