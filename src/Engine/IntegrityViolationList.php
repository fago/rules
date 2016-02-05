<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\IntegrityViolationList.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Collection of integrity violations.
 */
class IntegrityViolationList extends \ArrayIterator {

  /**
   * {@inheritdoc}
   */
  public function add(IntegrityViolation $violation) {
    $this[] = $violation;
  }

  /**
   * {@inheritdoc}
   */
  public function addAll(IntegrityViolationList $other_list) {
    foreach ($other_list as $violation) {
      $this[] = $violation;
    }
  }

  /**
   * Creates a new violation with the message and adds it to this list.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   The violation message.
   * @param string $uuid
   *   (Optional) UUID of the expression where the violation occurred.
   */
  public function addViolationWithMessage(TranslatableMarkup $message, $uuid = NULL) {
    $violation = new IntegrityViolation();
    $violation->setMessage($message);
    $violation->setUuid($uuid);
    $this[] = $violation;
  }

  /**
   * {@inheritdoc}
   */
  public function getFor($uuid) {
    $uuid_violations = [];
    foreach ($this as $violation) {
      if ($violation->getUuid() === $uuid) {
        $uuid_violations[] = $violation;
      }
    }
    return $uuid_violations;
  }

}
