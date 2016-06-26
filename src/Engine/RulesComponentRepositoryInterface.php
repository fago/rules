<?php

namespace Drupal\rules\Engine;

/**
 * Interface for the component repository.
 *
 * The component repository provides the API for fetching executable Rules
 * components from cache.
 */
interface RulesComponentRepositoryInterface {

  /**
   * Adds a component resolver.
   *
   * @param \Drupal\rules\Engine\RulesComponentResolverInterface $resolver
   *   The resolver.
   * @param string $resolver_name
   *   The name under which to add the resolver.
   *
   * @return $this
   */
  public function addComponentResolver(RulesComponentResolverInterface $resolver, $resolver_name);

  /**
   * Gets the component for the given ID.
   *
   * @param string $id
   *   The ID of the component to get. The supported IDs depend on the given
   *   provider. For the default provider 'rules' the entity IDs of component
   *   configs may be passed.
   * @param string $resolver
   *   The resolver of the component. Supported values are:
   *   - rules_component: (Default) The component configs identified by their
   *     ID.
   *   - rules_event: The aggregated components of all reaction rules configured
   *     for an event, identified by the event name; e.g.,
   *     'rules_entity_presave'.
   *   Note, that modules may add further resolvers via tagged services. Check
   *   the rules.services.yml for an example.
   *
   * @return \Drupal\rules\Engine\RulesComponent|null
   *   The component, or NULL if it is not existing.
   *
   * @throws \Drupal\rules\Exception\InvalidArgumentException
   *   Thrown if an unsupported provider is given.
   */
  public function get($id, $resolver = 'rules_component');

  /**
   * Gets the components for the given IDs.
   *
   * @param string[] $ids
   *   The IDs of the components to get. The supported IDs depend on the given
   *   provider. For the default provider 'rules' the entity IDs of component
   *   configs may be passed.
   * @param string $resolver
   *   The resolver of the component. See ::get() for a list of supported
   *   resolvers.
   *
   * @return \Drupal\rules\Engine\RulesComponent[]
   *   An array of components, keyed by component ID.
   *
   * @throws \Drupal\rules\Exception\InvalidArgumentException
   *   Thrown if an unsupported provider is given.
   */
  public function getMultiple(array $ids, $resolver = 'rules_component');

}
