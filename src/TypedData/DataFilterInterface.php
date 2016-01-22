<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\DataFilterInterface.
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Interface for data filters.
 *
 * Data filters allow filtering data values by a simple, possibly user inputted
 * string like default(0), or format_date('short'). Data filters are may be
 * used on placeholder replacement values.
 *
 * @see \Drupal\rules\TypedData\PlaceholderResolverInterface
 */
interface DataFilterInterface {

  /**
   * Filters the given data value.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The definition of the filtered data.
   * @param mixed $value
   *   The value for which to apply the filter.
   * @param array $arguments
   *   The array of filter arguments.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) An object to which required bubbleable metadata will be added.
   *
   * @return mixed
   *   The resulting data value.
   */
  public function filter(DataDefinitionInterface $definition, $value, array $arguments, BubbleableMetadata $bubbleable_metadata = NULL);

  /**
   * Determines whether data based upon the given definition can be filtered.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The definition of the filtered data.
   *
   * @return bool
   *   Whether the data can be filtered.
   */
  public function canFilter(DataDefinitionInterface $definition);

  /**
   * Describes the data after applying the filter.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The definition of the filtered data.
   * @param string[] $arguments
   *   The array of filter arguments.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   *   The definition of the resulting data.
   */
  public function filtersTo(DataDefinitionInterface $definition, array $arguments);

  /**
   * Gets the number of required arguments.
   *
   * @return int
   *   The number of required arguments.
   */
  public function getNumberOfRequiredArguments();

  /**
   * Defines whether the filter is able to process NULL values.
   *
   * @return bool
   *   Whether the filter is able to process NULL values.
   */
  public function allowsNullValues();

  /**
   * Suggests some possible argument values based on user input.
   *
   * This is used to provide sensible auto-completion.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The definition of the filtered data.
   * @param string[] $arguments
   *   The array of filter arguments, which have been already inputted.
   * @param string $input
   *   (optional) The filter argument currently being input. Defaults to an
   *   empty string.
   *
   * @return string[]
   *   An array of possible argument strings.
   */
  public function suggestArgument(DataDefinitionInterface $definition, array $arguments, $input = '');

  /**
   * Validates the inputted arguments.
   *
   * Determines whether the given arguments have a valid syntax and can be
   * applied to data of the given definition.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The definition of the filtered data.
   * @param string[] $arguments
   *   The array of filter arguments.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]|string[]
   *   An array of translated validation error messages. If the arguments are
   *   valid, an empty array must be returned.
   */
  public function validateArguments(DataDefinitionInterface $definition, array $arguments);

}
