<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextInterface.
 */

namespace Drupal\rules\Context;

use \Drupal\Component\Plugin\Context\ContextInterface as CoreContextInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Interface for context.
 */
interface ContextInterface extends CoreContextInterface {

  /**
   * Gets the context value as typed data object.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   */
  public function getContextData();

  /**
   * Sets the context value as typed data object.
   *
   * @return $this
   */
  public function setContextData(TypedDataInterface $data);
}
