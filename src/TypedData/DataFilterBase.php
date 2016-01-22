<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\DataFilterBase.
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataTrait;

/**
 * Base class for data filters.
 */
abstract class DataFilterBase implements DataFilterInterface {

  use TypedDataTrait;
  use StringTranslationTrait;

  /**
   * The filter id.
   *
   * @var string
   */
  protected $filterId;

  /**
   * The plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Constructs the object.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    $this->filterId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberOfRequiredArguments() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function allowsNullValues() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function suggestArgument(DataDefinitionInterface $definition, array $arguments, $input = '') {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateArguments(DataDefinitionInterface $definition, array $arguments) {
    $errors = [];
    if (count($arguments) < $this->getNumberOfRequiredArguments()) {
      $errors[] = $this->t('Missing arguments for filter %filter_id', ['%filter_id' => $this->filterId]);
    }
    return $errors;
  }

}
