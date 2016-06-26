<?php

namespace Drupal\rules\Engine;

/**
 * Interface for rules component resolvers.
 *
 * A resolver is responsible for getting components for a certain provider. The
 * component resolvers are added to the repository via tagged services and
 * provider name is determined.
 */
interface RulesComponentResolverInterface {

  /**
   * Gets multiple components.
   *
   * @param string[] $ids
   *   The list of IDs of the components to get.
   *
   * @return \Drupal\rules\Engine\RulesComponent[]
   *   The array of components that could be resolved, keyed by ID.
   */
  public function getMultiple(array $ids);

}
