<?php
// $Id$

/**
 * @file
 * API include file from the entity CRUD API.
 * See http://drupal.org/project/entity.
 */

/**
 * Interface for EntityControllers compatible with EntityCrud implementations.
 */
interface EntityCRUDControllerInterface extends DrupalEntityControllerInterface {

  /**
   * Delete permanently saved entities.
   *
   * @param $ids
   *   An array of entity IDs.
   */
  public function delete($ids);
}

/**
 * A controller that supports loading instances of the 'entity class' and
 * provides mass delete functionality.
 */
class EntityCRUDController extends DrupalDefaultEntityController implements EntityCRUDControllerInterface {

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

  /**
   * Overridden.
   * @see DrupalDefaultEntityController#load($ids, $conditions)
   *
   * In contrast to the parent implementation we factor out query execution, so
   * fetching can be further customized easily.
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
      $entities += $this->cacheGet($this->ids, $this->conditions);
      // If any entities were loaded, remove them from the ids still to load.
      if ($passed_ids) {
        $this->ids = array_keys(array_diff_key($passed_ids, $entities));
      }
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
        $queried_entities[$record->{$this->idKey}] = $record;
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
      // Ensure that the returned array is ordered the same as the original
      // $ids array if this was passed in and remove any invalid ids.
      if ($passed_ids) {
        // Remove any invalid ids from the array.
        $passed_ids = array_intersect_key($passed_ids, $entities);
        foreach ($entities as $entity) {
          $passed_ids[$entity->{$this->idKey}] = $entity;
        }
        $entities = $passed_ids;
      }
    }
    return $entities;
  }

  /**
   * Implements EntityCRUDControllerInterface.
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
}

/**
 * Delete permanently saved entities.
 *
 * @param $entity_type
 *   The entity type to delete, e.g. node or user.
 * @param $ids
 *   An array of entity IDs.
 */
function entity_delete($entity_type, array $ids) {
  entity_get_controller($entity_type)->delete($ids);
}


/**
 * Provides a unique interface for CRUD operations on entites.
 */
interface EntityCRUDInterface {

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
   * Returns the identifier of this instance.
   *
   * @return
   *   The identifier or NULL, if this entity hasn't been saved yet.
   */
  public function identifier();

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

/**
 * A common class for db entities.
 */
class EntityDB extends FacesExtendable implements EntityCRUDInterface {

  protected $entityType;
  protected $entityInfo;
  protected $idKey;

  public function __construct(array $values = array(), $entityType = NULL) {
    if (empty($entityType)) {
      throw new Exception('Cannot created an instance of EntityDB without a specified entity type.');
    }
    $this->entityType = $entityType;
    $this->entityInfo = entity_get_info($entityType);
    $this->idKey = $this->entityInfo['object keys']['id'];
    // Set initial values.
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  public function identifier() {
    return isset($this->{$this->idKey}) ? $this->{$this->idKey} : NULL;
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
    $id = $this->identifier();
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
    module_invoke_all($this->entityType . '_' . $hook, $this);
  }

}

