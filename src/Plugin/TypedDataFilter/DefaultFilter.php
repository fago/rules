<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\TypedDataFilter\DefaultFilter.
 */

namespace Drupal\rules\Plugin\TypedDataFilter;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\rules\TypedData\DataFilterBase;

/**
 * A data filter lowering all string characters.
 *
 * @DataFilter(
 *   id = "default",
 *   label = @Translation("Applies a default-value if there is no value."),
 * )
 */
class DefaultFilter extends DataFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(DataDefinitionInterface $definition, $value, array $arguments, BubbleableMetadata $bubbleable_metadata = NULL) {
    return isset($value) ? $value : $arguments[0];
  }

  /**
   * {@inheritdoc}
   */
  public function canFilter(DataDefinitionInterface $definition) {
    return is_subclass_of($definition->getClass(), StringInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function filtersTo(DataDefinitionInterface $definition, array $arguments) {
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function allowsNullValues() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberOfRequiredArguments() {
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function validateArguments(DataDefinitionInterface $definition, array $arguments) {
    $errors = parent::validateArguments($definition, $arguments);
    if (isset($arguments[0])) {
      // Ensure the provided value is given for this data.
      $violations = $this->getTypedDataManager()
        ->create($definition, $arguments[0])
        ->validate();
      foreach ($violations as $violation) {
        $errors[] = $violation->getMessage();
      }
    }
    return $errors;
  }

}
