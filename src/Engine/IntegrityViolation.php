<?php

namespace Drupal\rules\Engine;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Holds information about an integrity violation in a Rules expression.
 */
class IntegrityViolation implements IntegrityViolationInterface {

  /**
   * The user facing message of this violation.
   *
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected $message;

  /**
   * The associated context name (optional).
   *
   * @var string
   */
  protected $contextName;

  /**
   * The UUID of the expression where the violation occurred.
   *
   * @var string
   */
  protected $uuid;

  /**
   * {@inheritdoc}
   */
  public function setMessage(TranslatableMarkup $message) {
    $this->message = $message;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * {@inheritdoc}
   */
  public function setContextName($context_name) {
    $this->contextName = $context_name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextName() {
    return $this->contextName;
  }

  /**
   * {@inheritdoc}
   */
  public function setUuid($uuid) {
    $this->uuid = $uuid;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUuid() {
    return $this->uuid;
  }

}
