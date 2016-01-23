<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExecutionMetadataState.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\rules\Exception\RulesIntegrityException;

/**
 * The state used during configuration time holding data definitions.
 */
class ExecutionMetadataState implements ExecutionMetadataStateInterface {

  use TypedDataTrait;

  /**
   * The known data definitions.
   *
   * @var \Drupal\Core\TypedData\DataDefinitionInterface
   */
  protected $dataDefinitions = [];

  /**
   * {@inheritdoc}
   */
  public static function create($data_definitions = []) {
    return new static($data_definitions);
    // @todo Initialize the global "site" variable.
  }

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $data_definitions
   *   (optional) Data definitions to initialize this state with.
   */
  protected function __construct($data_definitions) {
    $this->dataDefinitions = $data_definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function addDataDefinition($name, DataDefinitionInterface $definition) {
    $this->dataDefinitions[$name] = $definition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataDefinition($name) {
    if (!array_key_exists($name, $this->dataDefinitions)) {
      throw new RulesIntegrityException("Unable to get variable $name, it is not defined.");
    }
    return $this->dataDefinitions[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function hasDataDefinition($name) {
    return array_key_exists($name, $this->dataDefinitions);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchDefinitionByPropertyPath($property_path, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    try {
      $parts = explode('.', $property_path);
      $var_name = array_shift($parts);
      return $this
        ->getTypedDataManager()
        ->getDataFetcher()
        ->fetchDefinitionBySubPaths($this->getDataDefinition($var_name), $parts, $langcode);
    }
    catch (\InvalidArgumentException $e) {
      // Pass on the original exception in the exception trace.
      throw new RulesIntegrityException($e->getMessage(), 0, $e);
    }
  }

}
