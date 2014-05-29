<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextDefinitionInterface.
 */

namespace Drupal\rules\Context;

/**
 * Interface for context definitions.
 */
interface ContextDefinitionInterface {

  /**
   * Returns a human readable label.
   *
   * @return string
   *   The label.
   */
  public function getLabel();

  /**
   * Returns a human readable description.
   *
   * @return string|null
   *   The description, or NULL if no description is available.
   */
  public function getDescription();

  /**
   * Returns the data type required by the context.
   *
   * If the context is multiple-valued, this represents the type of each value.
   *
   * @return string
   *   The data type.
   */
  public function getDataType();

  /**
   * Returns whether the data is multi-valued, i.e. a list of data items.
   *
   * This is equivalent to checking whether the data definition implements the
   * \Drupal\Core\TypedData\ListDefinitionInterface interface.
   *
   * @return bool
   *   Whether the data is multi-valued; i.e. a list of data items.
   */
  public function isMultiple();

  /**
   * Determines whether the context is required.
   *
   * For required data a non-NULL value is mandatory.
   *
   * @return bool
   *   Whether a data value is required.
   */
  public function isRequired();

  /**
   * Returns an array of validation constraints.
   *
   * See \Drupal\Core\TypedData\TypedDataManager::getConstraints() for details.
   *
   * @return array
   *   An array of validation constraint definitions, keyed by constraint name.
   *   Each constraint definition can be used for instantiating
   *   \Symfony\Component\Validator\Constraint objects.
   */
  public function getConstraints();

  /**
   * Returns a validation constraint.
   *
   * See \Drupal\Core\TypedData\TypedDataManager::getConstraints() for details.
   *
   * @param string $constraint_name
   *   The name of the the constraint, i.e. its plugin id.
   *
   * @return array
   *   A validation constraint definition which can be used for instantiating a
   *   \Symfony\Component\Validator\Constraint object.
   */
  public function getConstraint($constraint_name);

  /**
   * Returns the data definition of the defined context.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   */
  public function getDataDefinition();

}