<?php
// $Id$

/**
 * @file
 * API include file from the entity API.
 * See http://drupal.org/project/entity.
 */

/**
 * Defines status codes used for exportable entities.
 */
if (!defined('ENTITY_IN_DB')) {
  /**
   * A bit flag used to let us know if an entity is in the database.
   */
  define('ENTITY_IN_DB', 0x01);

  /**
   * A bit flag used to let us know if an entity is a 'default' in code.
   */
  define('ENTITY_IN_CODE', 0x02);

  /**
   * A bit flag used to mark entities as fixed, thus not changeable for any
   * user.
   */
  define('ENTITY_FIXED', 0x04);
}

if (!interface_exists('EntityAPIControllerInterface')) {
  /**
   * Interface for EntityControllers compatible with the entity API.
   */
  interface EntityAPIControllerInterface extends DrupalEntityControllerInterface {

    /**
     * Delete permanently saved entities.
     *
     * @param $ids
     *   An array of entity IDs.
     */
    public function delete($ids);
  }

  /**
   * Provides an interface for entites using the entity API.
   */
  interface EntityAPIInterface {

    /**
     * Permanently save this entity.
     *
     * @return
     *   Failure to write a record will return FALSE. Otherwise SAVED_NEW or
     *   SAVED_UPDATED is returned depending on the operation performed.
     */
    public function save();

    /**
     * Permanently delete this entity.
     */
    public function delete();

    /**
     * Returns the internal identifier of this instance.
     *
     * @return
     *   The primary ID or NULL, if this entity hasn't been saved yet.
     */
    public function internalIdentifier();

    /**
     * Returns the entity type of this instance.
     *
     * @return
     *   The name of the entity type of this instance.
     */
    public function entityType();

    /**
     * Returns the entity info for this instance as returned by entity_get_info().
     *
     * @return
     *   An array of entity info for this entity type.
     *
     * @see
     *   entity_get_info()
     */
    public function entityInfo();

    /**
     * Invokes a hook on behalf the entity. For hooks that have a respective
     * field API attacher like insert/update/.. the attacher is called too.
     */
    public function invoke($hook);

  }
}

/**
 * A controller that supports loading instances of the 'entity class' and
 * provides mass delete functionality.
 */
class EntityAPIController extends DrupalDefaultEntityController implements EntityAPIControllerInterface {

  protected $defaultEntities;
  protected $nameKey, $statusKey;

  /**
   * Overridden.
   * @see DrupalDefaultEntityController#__construct()
   *
   * Allows specifying a name key serving as uniform identifier for this entity
   * type while still internally we are using numeric identifieres.
   */
  public function __construct($entityType) {
    parent::__construct($entityType);
    // Use the name key as primary identifier.
    $this->nameKey = isset($this->entityInfo['object keys']['name']) ? $this->entityInfo['object keys']['name'] : $this->idKey;
    if (isset($this->entityInfo['exportable'])) {
      $this->statusKey = isset($this->entityInfo['export']['status key']) ? $this->entityInfo['export']['status key'] : 'status';
    }
  }

  /**
   * Builds and executes the query for loading.
   *
   * @return The results in a Traversable object.
   */
  public function query() {
    // Build the query.
    $this->buildQuery();
    $result = $this->query->execute();
    if (!empty($this->entityInfo['entity class'])) {
      $result->setFetchMode(PDO::FETCH_CLASS, $this->entityInfo['entity class'], array(array(), $this->entityType));
    }
    return $result;
  }

  protected function buildQuery() {
    // If we have a separate name key use it to filter for ids in $this->ids.
    if ($this->ids && $this->nameKey != $this->idKey) {
      $ids = $this->ids;
      $this->ids = array();
      parent::buildQuery();
      $this->query->condition("base.{$this->nameKey}", $ids, 'IN');
    }
    else {
      parent::buildQuery();
    }
  }

  /**
   * Overridden.
   * @see DrupalDefaultEntityController#load($ids, $conditions)
   *
   * In contrast to the parent implementation we factor out query execution, so
   * fetching can be further customized easily. Also we add any in code defined
   * objects.
   */
  public function load($ids = array(), $conditions = array()) {
    $this->ids = $ids;
    $this->conditions = $conditions;

    $entities = array();

    // Revisions are not statically cached, and require a different query to
    // other conditions, so separate the revision id into its own variable.
    if ($this->revisionKey && isset($this->conditions[$this->revisionKey])) {
      $this->revisionId = $this->conditions[$this->revisionKey];
      unset($this->conditions[$this->revisionKey]);
    }
    else {
      $this->revisionId = FALSE;
    }

    // Create a new variable which is either a prepared version of the $ids
    // array for later comparison with the entity cache, or FALSE if no $ids
    // were passed. The $ids array is reduced as items are loaded from cache,
    // and we need to know if it's empty for this reason to avoid querying the
    // database when all requested entities are loaded from cache.
    $passed_ids = !empty($this->ids) ? array_flip($this->ids) : FALSE;
    // Try to load entities from the static cache, if the entity type supports
    // static caching.
    if ($this->cache) {
      $entities = $this->cacheGet($this->ids, $this->conditions);
      // If any entities were loaded, remove them from the ids still to load.
      if ($passed_ids) {
        $this->ids = array_keys(array_diff_key($passed_ids, $entities));
      }
    }
    if (!empty($this->entityInfo['exportable'])) {
      // Add default entities defined in code.
      $entities += $this->getDefaults($this->ids, $this->conditions);
    }

    // Load any remaining entities from the database. This is the case if $ids
    // is set to FALSE (so we load all entities), if there are any ids left to
    // load, if loading a revision, or if $conditions was passed without $ids.
    if ($this->ids === FALSE || $this->ids || $this->revisionId || ($this->conditions && !$passed_ids)) {
      $schema = drupal_get_schema($this->entityInfo['base table']);
      $queried_entities = array();
      foreach ($this->query() as $record) {
        // Care for serialized columns.
        foreach ($schema['fields'] as $field => $info) {
          if (!empty($info['serialize']) && isset($record->$field)) {
            $record->$field = unserialize($record->$field);
            // Support automatic merging of 'data' fields into the entity.
            if (!empty($info['merge']) && is_array($record->$field)) {
              foreach ($record->$field as $key => $value) {
                $record->$key = $value;
              }
              unset($record->$field);
            }
          }
        }
        if (isset($this->statusKey)) {
          // Care for setting the status key properly.
          $record->{$this->statusKey} |= ENTITY_IN_DB;
          $id = $record->{$this->nameKey};
          if (isset($entities[$id]) && $entities[$id]->{$this->statusKey} & ENTITY_IN_CODE) {
            $record->{$this->statusKey} |= ENTITY_IN_CODE;
            unset($entities[$id]);
          }
        }
        $queried_entities[$record->{$this->nameKey}] = $record;
      }
    }

    // Pass all entities loaded from the database through $this->attachLoad(),
    // which attaches fields (if supported by the entity type) and calls the
    // entity type specific load callback, for example hook_node_load().
    if (!empty($queried_entities)) {
      $this->attachLoad($queried_entities);
      $entities += $queried_entities;
    }

    if ($this->cache) {
      // Add entities to the cache if we are not loading a revision.
      if (!empty($queried_entities) && !$this->revisionId) {
        $this->cacheSet($queried_entities);
      }
    }
    // Ensure that the returned array is ordered the same as the original
    // $ids array if this was passed in and remove any invalid ids.
    if ($passed_ids) {
      // Remove any invalid ids from the array.
      $passed_ids = array_intersect_key($passed_ids, $entities);
      foreach ($entities as $entity) {
        $passed_ids[$entity->{$this->nameKey}] = $entity;
      }
      $entities = $passed_ids;
    }
    return $entities;
  }

  /**
   * Implement EntityAPIControllerInterface.
   */
  public function delete($ids) {
    $entities = $this->load($ids);

    db_delete($this->entityInfo['base table'])
      ->condition($this->idKey, $ids, 'IN')
      ->execute();

    foreach ($entities as $entity) {
      $entity->invoke('delete');
    }
    $this->resetCache();
  }

  /**
   * For exportables call the hook to get all default entities.
   */
  protected function getDefaults($ids, $conditions = array()) {
    if (!isset($this->defaultEntities)) {
      $this->defaultEntities = array();
      if (!empty($this->entityInfo['exportable'])) {
        $this->entityInfo += array('export' => array());
        $this->entityInfo['export'] += array('default hook' => 'default_' . $this->entityType);
        if ($hook = $this->entityInfo['export']['default hook']) {
          $this->defaultEntities = module_invoke_all($hook);
          drupal_alter($hook, $this->defaultEntities);
          foreach ($this->defaultEntities as $entity) {
            $entity->{$this->statusKey} |= ENTITY_IN_CODE;
          }
        }
      }
    }
    $entities = $ids ? array_intersect_key($this->defaultEntities, array_flip($ids)) : $this->defaultEntities;
    return $this->applyConditions($entities, $conditions);
  }

  protected function applyConditions($entities, $conditions = array()) {
    if ($conditions) {
      foreach ($entities as $key => $entity) {
        $entity_values = (array) $entity;
        if (array_diff_assoc($conditions, $entity_values)) {
          unset($entities[$key]);
        }
      }
    }
    return $entities;
  }

  /**
   * Overridden.
   * @see includes/DrupalDefaultEntityController#cacheGet($ids, $conditions)
   *
   * If there is nameKey given, we index our entities by this key. This
   * overrides cacheGet() to respect that when applying $conditions.
   */
  protected function cacheGet($ids, $conditions = array()) {
    return $this->applyConditions(parent::cacheGet($ids), $conditions);
  }

  public function resetCache() {
    $this->entityCache = array();
    unset($this->defaultEntities);
  }
}

/**
 * A common class for db entities.
 */
class EntityDB extends FacesExtendable implements EntityAPIInterface {

  protected $entityType;
  protected $entityInfo;
  protected $idKey, $nameKey, $bundleKey;

  public function __construct(array $values = array(), $entityType = NULL) {
    if (empty($entityType)) {
      throw new Exception('Cannot created an instance of EntityDB without a specified entity type.');
    }
    $this->entityType = $entityType;
    $this->entityInfo = entity_get_info($entityType);
    $this->idKey = $this->entityInfo['object keys']['id'];
    $this->nameKey = isset($this->entityInfo['object keys']['name']) ? $this->entityInfo['object keys']['name'] : $this->idKey;
    // If this is the bundle of another entity, set the bundle key.
    if (isset($this->entityInfo['bundle of'])) {
      $info = entity_get_info($this->entityInfo['bundle of']);
      $this->bundleKey = $info['bundle keys']['bundle'];
    }
    // Set initial values.
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  public function internalIdentifier() {
    return isset($this->{$this->idKey}) ? $this->{$this->idKey} : NULL;
  }

  public function identifier() {
    return isset($this->{$this->nameKey}) ? $this->{$this->nameKey} : NULL;
  }

  public function entityInfo() {
    return $this->entityInfo;
  }

  public function entityType() {
    return $this->entityType;
  }

  public function save() {
    $this->invoke('presave');

    if (isset($this->{$this->idKey})) {
      $return = drupal_write_record($this->entityInfo['base table'], $this, $this->idKey);
      $this->invoke('update');
    }
    else {
      $return = drupal_write_record($this->entityInfo['base table'], $this);
      $this->invoke('insert');
    }
    return $return;
  }

  public function delete() {
    $id = $this->internalIdentifier();
    if (isset($id)) {
      db_delete($this->entityInfo['base table'])
        ->condition($this->idKey, $id)
        ->execute();
      $this->invoke('delete');
      entity_get_controller($this->entityType)->resetCache();
    }
  }

  /**
   * Invokes a hook and calls any field API attachers.
   */
  public function invoke($hook) {
    if (!empty($this->entityInfo['fieldable']) && function_exists($function = 'field_attach_' . $hook)) {
      $function($this->entityType, $this);
    }
    if (isset($this->entityInfo['bundle of']) && $type = $this->entityInfo['bundle of']) {
      // Call field API bundle attachers for the entity we are a bundle of.
      if ($hook == 'insert') {
        field_attach_create_bundle($type, $this->{$this->bundleKey});
      }
      elseif ($hook == 'delete') {
        field_attach_delete_bundle($type, $this->{$this->bundleKey});
      }
      elseif ($hook == 'update' && $id = $this->{$this->idKey}) {
        $entities = entity_load($this->entityType, array($id));
        if ($entities[$id]->{$this->bundleKey} != $this->{$this->bundleKey}) {
          field_attach_rename_bundle($type, $entities[$id]->{$this->bundleKey}, $this->{$this->bundleKey});
        }
      }
    }
    module_invoke_all($this->entityType . '_' . $hook, $this);
  }

}

