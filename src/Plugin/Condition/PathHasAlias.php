<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\PathHasAlias.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TypedData;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Path has alias' condition.
 *
 * @Condition(
 *   id = "rules_path_has_alias",
 *   label = @Translation("Path has alias")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class PathHasAlias extends RulesConditionBase implements ContainerFactoryPluginInterface {

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
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The alias manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TypedDataManager $typed_data_manager, AliasManagerInterface $alias_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $typed_data_manager);
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
      $container->get('typed_data_manager'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['path'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Path'));

    $contexts['language'] = ContextDefinition::create($typed_data_manager, 'language')
      ->setLabel(t('Language'))
      ->setDescription(t('If specified, the language for which the URL alias applies.'))
      ->setRequired(FALSE);

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Path has alias');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $path = $this->getContextValue('path');
    $language = $this->getContext('language')->getContextData() ? $this->getContextValue('language')->getId() : NULL;
    $alias = $this->aliasManager->getAliasByPath($path, $language);
    return $alias != $path;
  }

}
