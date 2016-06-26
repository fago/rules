<?php

namespace Drupal\rules\Context;

use Drupal\rules\Engine\ExecutionStateInterface;

/**
 * Interface for Rules data processor plugins.
 */
interface DataProcessorInterface {

  /**
   * Process the given value.
   *
   * @param mixed $value
   *   The value to process.
   * @param \Drupal\rules\Engine\ExecutionStateInterface $rules_state
   *   The current Rules execution state containing all context variables.
   *
   * @return mixed
   *   The processed value. Since the value can also be a primitive data type
   *   (a string for example) this function must return the value.
   *
   * @throws \Drupal\rules\Exception\EvaluationException
   *   Thrown when the data cannot be processed.
   */
  public function process($value, ExecutionStateInterface $rules_state);

}
