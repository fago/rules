<?php

namespace Drupal\rules\Engine;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rules\Exception\OutOfBoundsException;

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
   * Returns the violation at a given offset.
   *
   * @param int $offset
   *   The offset of the violation.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationInterface
   *   The violation.
   *
   * @throws \Drupal\rules\Exception\OutOfBoundsException
   *   Thrown if the offset does not exist.
   */
  public function get($offset) {
    try {
      return $this->offsetGet($offset);
    }
    catch (\OutOfBoundsException $e) {
      throw new OutOfBoundsException();
    }
  }

  /**
   * Returns whether the given offset exists.
   *
   * @param int $offset
   *   The violation offset.
   *
   * @return bool
   *   Whether the offset exists.
   */
  public function has($offset) {
    return $this->offsetExists($offset);
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
