<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\PathAliasExists.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Path alias exists' condition.
 *
 * @Condition(
 *   id = "rules_path_alias_exists",
 *   label = @Translation("Path alias exists"),
 *   category = @Translation("Path"),
 *   context = {
 *     "alias" = @ContextDefinition("string",
 *       label = @Translation("Path alias"),
 *       description = @Translation("Specify the path alias to check for. For example, 'about' for an about page.")
 *     ),
 *     "language" = @ContextDefinition("language",
 *       label = @Translation("Language"),
 *       description = @Translation("If specified, the language for which the URL alias applies."),
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class PathAliasExists extends RulesConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The alias manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathAliasExists object.
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
   * {@inheritdoc}
   */
  public function evaluate() {
    $alias = $this->getContextValue('alias');
    $language = $this->getContext('language')->hasContextValue() ? $this->getContextValue('language')->getId() : NULL;
    $path = $this->aliasManager->getPathByAlias($alias, $language);
    return $path != $alias;
  }

}
