<?php

namespace Drupal\rules\Context;

use Drupal\Core\Plugin\Context\ContextRepositoryInterface;

/**
 * Trait for classes that depend on the global context repository.
 */
trait GlobalContextRepositoryTrait {

  /**
   * The global context repository.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * Sets the global context repository.
   *
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The global context repository.
   *
   * @return $this
   */
  public function setGlobalContextRepository(ContextRepositoryInterface $context_repository) {
    $this->contextRepository = $context_repository;
    return $this;
  }

  /**
   * Gets the global context repository.
   *
   * @return \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   *   The context repository.
   */
  public function getGlobalContextRepository() {
    if (empty($this->contextRepository)) {
      $this->contextRepository = \Drupal::service('context.repository');
    }
    return $this->contextRepository;
  }

}
