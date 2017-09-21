<?php

namespace Drupal\rules\Engine;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * The state used during configuration time holding data definitions.
 *
 * This is mostly used for integrity checks to validate the configuration of a
 * rule. The metadata state is passed down the expression tree where data
 * definitions can be modified or added. Nested expression in the tree then get
 * the updated metadata state and can make use of the updated variable data
 * definitions.
 */
interface ExecutionMetadataStateInterface {

  /**
   * Creates the object.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $data_definitions
   *   (optional) Data definitions to initialize this state with.
   *
   * @return static
   */
  public static function create(array $data_definitions = []);

  /**
   * Sets a data definition in the execution metadata state.
   *
   * @param string $name
   *   Variable name of the data definition.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The data definition that represents the variable.
   */
  public function setDataDefinition($name, DataDefinitionInterface $definition);

  /**
   * Retrieve a data definition in this execution metadata state.
   *
   * @param string $name
   *   The variable name to get the data definition for.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   *   The data definition.
   */
  public function getDataDefinition($name);

  /**
   * Checks if the variable with the given name is present in the state.
   *
   * @param string $name
   *   The variable name.
   *
   * @return bool
   *   TRUE if the state has that variable, FALSE otherwise.
   */
  public function hasDataDefinition($name);

  /**
   * Removes a data definition from the metadata state.
   *
   * @param string $name
   *   Variable name of the data definition to be removed.
   *
   * @return $this
   */
  public function removeDataDefinition($name);

  /**
   * Applies a data selector and returns the corresponding data definition.
   *
   * @param string $property_path
   *   The property path, example: "node:title:value".
   * @param string $langcode
   *   The language code.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   *   A data definition if the property path could be applied.
   *
   * @throws \Drupal\rules\Exception\IntegrityException
   *   Thrown if the property path is invalid.
   */
  public function fetchDefinitionByPropertyPath($property_path, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED);

}
