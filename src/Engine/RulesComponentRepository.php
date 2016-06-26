<?php

namespace Drupal\rules\Engine;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rules\Exception\InvalidArgumentException;

/**
 * Implements the component repository interface.
 */
class RulesComponentRepository implements RulesComponentRepositoryInterface {

  /**
   * Array of component resolvers, keyed by provider.
   *
   * @var \Drupal\rules\Engine\RulesComponentResolverInterface[]
   */
  protected $resolvers = [];

  /**
   * Static cache of loaded components.
   *
   * The array is keyed by cache ID.
   *
   * @var \Drupal\rules\Engine\RulesComponent[]
   */
  protected $components = [];

  /**
   * Cache backend instance.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The current language code.
   *
   * @var string
   */
  protected $langcode;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(CacheBackendInterface $cache_backend, LanguageManagerInterface $language_manager) {
    $this->cacheBackend = $cache_backend;
    $this->languageManager = $language_manager;
    $this->langcode = $this->languageManager->getCurrentLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function addComponentResolver(RulesComponentResolverInterface $resolver, $resolver_name) {
    $this->resolvers[$resolver_name] = $resolver;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($id, $provider = 'rules_component') {
    $result = $this->getMultiple([$id], $provider);
    return reset($result) ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $ids, $provider = 'rules_component') {
    if (!isset($this->resolvers[$provider])) {
      throw new InvalidArgumentException("Invalid component provider $provider given.");
    }
    $cids = [];
    foreach ($ids as $id) {
      $cids[$id] = "$provider:$id:$this->langcode";
    }
    $results = array_intersect_key($this->components, array_flip($cids));
    $cids_missing = array_diff_assoc($cids, array_keys($results));

    if ($cids_missing) {
      // Note that the cache backend removes resolved IDs.
      $cache_results = $this->cacheBackend->getMultiple($cids_missing);
      foreach ($cache_results as $cid => $cache_result) {
        $this->components[$cid] = $cache_result->data;
        $results[$cid] = $cache_result->data;
      }
      if ($cids_missing) {
        $resolved_results = $this->resolvers[$provider]->getMultiple(array_keys($cids_missing));
        $cache_items = [];
        foreach ($resolved_results as $id => $component) {
          $cid = $cids[$id];
          $this->components[$cid] = $component;
          $results[$cid] = $component;
          $cache_items[$cid]['data'] = $component;
        }
        // Cache entries to speed up future lookups.
        $this->cacheBackend->setMultiple($cache_items);
      }
    }
    return $results;
  }

}
