<?php

namespace Drupal\rules\Engine;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Describes a violation of the integrity of a rule.
 *
 * Example: if the data selector node:field_example is used in an action but
 * there is no "node" variable available in the context of the rule then a
 * violation is raised during the integrity check of the rule.
 */
interface IntegrityViolationInterface {

  /**
   * Sets the user facing message that can be displayed for this violation.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   The message.
   *
   * @return $this
   */
  public function setMessage(TranslatableMarkup $message);

  /**
   * Returns the translated message of this violation.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The message.
   */
  public function getMessage();

  /**
   * Sets the context name when this violation should be associated to one.
   *
   * @param string $context_name
   *   The context name.
   *
   * @return $this
   */
  public function setContextName($context_name);

  /**
   * Returns the associated context name, if there is one.
   *
   * A violation can relate to a specific context that is used in the
   * expression.
   *
   * @return string|null
   *   The context name or NULL if the violation is not specific to a context.
   */
  public function getContextName();

  /**
   * Sets the UUID of the nested expression where this violation occurred.
   *
   * @param string $uuid
   *   The UUID.
   *
   * @return $this
   */
  public function setUuid($uuid);

  /**
   * Returns the UUID of the expression this violation belongs to.
   *
   * @return string
   *   The UUID.
   */
  public function getUuid();

}
