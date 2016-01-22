<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\IntegrityViolationList.
 */

namespace Drupal\rules\Engine;

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
