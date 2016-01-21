<?php

/**
 * @file
 * Contains  Drupal\rules\TypedData\Exception\FilterException.
 */

namespace Drupal\rules\TypedData\Exception;

/**
 * Exception thrown when filters cannot be applied.
 *
 * Data filters should provide separate exception classes for any possible
 * problem.
 */
abstract class FilterException extends \Exception {}
