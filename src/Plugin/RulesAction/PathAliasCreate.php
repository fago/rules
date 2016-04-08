<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Create any path alias' action.
 *
 * @RulesAction(
 *   id = "rules_path_alias_create",
 *   label = @Translation("Create any path alias"),
 *   category = @Translation("Path"),
 *   context = {
 *     "source" = @ContextDefinition("string",
 *       label = @Translation("Existing system path"),
 *       description = @Translation("Specifies the existing path you wish to alias. For example: node/28, forum/1, taxonomy/term/1+2.")
 *     ),
 *     "alias" = @ContextDefinition("string",
 *       label = @Translation("Path alias"),
 *       description = @Translation("Specify an alternative path by which this data can be accessed. For example, 'about' for an about page. Use a relative path and do not add a trailing slash.")
 *     ),
 *     "language" = @ContextDefinition("language",
 *       label = @Translation("Language"),
 *       description = @Translation("If specified, the language for which the path alias applies."),
 *       default_value = NULL,
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class PathAliasCreate extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The alias storage service.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * Constructs a PathAliasCreate object.
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
   * Creates an alias for an existing path.
   *
   * @param string $source
   *   The existing path that should be aliased.
   * @param string $alias
   *   The alias path that should be created.
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   (optional) The language.
   */
  protected function doExecute($source, $alias, LanguageInterface $language = NULL) {
    $langcode = isset($language) ? $language->getId() : LanguageInterface::LANGCODE_NOT_SPECIFIED;
    $this->aliasStorage->save($source, $alias, $langcode);
  }

}
