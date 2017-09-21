<?php

namespace Drupal\rules\Context;

use Drupal\Core\Plugin\Context\ContextDefinitionInterface as ContextDefinitionInterfaceCore;

/**
 * Context definition information required by Rules.
 *
 * The core interface is extended to add properties that are necessary for
 * Rules.
 */
interface ContextDefinitionInterface extends ContextDefinitionInterfaceCore {

  /**
   * Constants for the context assignment restriction mode.
   *
   * @see ::getAssignmentRestriction()
   */
  const ASSIGNMENT_RESTRICTION_INPUT = 'input';
  const ASSIGNMENT_RESTRICTION_SELECTOR = 'selector';

  /**
   * Determines if the context value is allowed to be NULL.
   *
   * @return bool
   *   TRUE if NULL values are allowed, FALSE otherwise.
   */
  public function isAllowedNull();

  /**
   * Sets the "allow NULL value" behavior.
   *
   * @param bool $null_allowed
   *   TRUE if NULL values should be allowed, FALSE otherwise.
   *
   * @return $this
   */
  public function setAllowNull($null_allowed);

  /**
   * Determines if this context has an assignment restriction.
   *
   * @return string|null
   *   Either ASSIGNMENT_RESTRICTION_INPUT for contexts that are only allowed to
   *   be provided as input values, ASSIGNMENT_RESTRICTION_SELECTOR for contexts
   *   that must be provided as data selectors or NULL if there is no
   *   restriction for this context.
   */
  public function getAssignmentRestriction();

  /**
   * Sets the assignment restriction mode for this context.
   *
   * @param string|null $restriction
   *   Either ASSIGNMENT_RESTRICTION_INPUT for contexts that are only allowed to
   *   be provided as input values, ASSIGNMENT_RESTRICTION_SELECTOR for contexts
   *   that must be provided as data selectors or NULL if there is no
   *   restriction for this context.
   */
  public function setAssignmentRestriction($restriction);

  /**
   * Exports the definition as an array.
   *
   * @return array
   *   An array with values for all definition keys.
   */
  public function toArray();

}
