<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\DataFilterBase
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataTrait;

/**
 * Base class for data filters.
 */
abstract class DataFilterBase implements DataFilterInterface {

  use TypedDataTrait;

  /**
   * {@inheritdoc}
   */
  public function getNumberOfRequiredArguments() {
    return 0;
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
    return [];
  }

}
