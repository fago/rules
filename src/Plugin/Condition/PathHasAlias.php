<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Path has alias' condition.
 *
 * @Condition(
 *   id = "rules_path_has_alias",
 *   label = @Translation("Path has alias"),
 *   category = @Translation("Path"),
 *   context = {
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("Path")
 *     ),
 *     "language" = @ContextDefinition("language",
 *       label = @Translation("Language"),
 *       description = @Translation("If specified, the language for which the URL alias applies."),
 *       default_value = NULL,
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class PathHasAlias extends RulesConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The alias manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathHasAlias object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The alias manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AliasManagerInterface $alias_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.alias_manager')
    );
  }

  /**
   * Check if a URL path has a URL alias.
   *
   * @param string $path
   *   The path to check.
   * @param \Drupal\Core\Language\LanguageInterface|null $language
   *   An optional language to look up the path in.
   *
   * @return bool
   *   TRUE if the path has an alias in the given language.
   */
  protected function doEvaluate($path, LanguageInterface $language = NULL) {
    $langcode = is_null($language) ? NULL : $language->getId();
    $alias = $this->aliasManager->getAliasByPath($path, $langcode);
    return $alias != $path;
  }

}
