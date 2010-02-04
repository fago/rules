<?php
// $Id$

/**
 * @file rules integration for the taxonomy_term module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implementation of hook_rules_event_info().
 */
function rules_taxonomy_event_info() {
  return array(
    'taxonomy_term_insert' => array(
      'label' => t('After saving a new term'),
      'group' => t('Taxonomy'),
      'variables' => array(
        'term' => array('type' => 'taxonomy_term', 'label' => t('created term')),
      ),
      'access callback' => 'rules_taxonomy_term_integration_access',
    ),
    'taxonomy_term_update' => array(
      'label' => t('After updating an existing term'),
      'group' => t('Taxonomy'),
      'variables' => array(
        'term' => array('type' => 'taxonomy_term', 'label' => t('updated term')),
      ),
      'access callback' => 'rules_taxonomy_term_integration_access',
    ),
    'taxonomy_term_delete' => array(
      'label' => t('After deleting a term'),
      'group' => t('Taxonomy'),
      'variables' => array(
        'term' => array('type' => 'taxonomy_term', 'label' => t('deleted term')),
      ),
      'access callback' => 'rules_taxonomy_term_integration_access',
    ),
    'taxonomy_vocabulary_insert' => array(
      'label' => t('After saving a new vocabulary'),
      'group' => t('Taxonomy'),
      'variables' => array(
        'vocabulary' => array('type' => 'taxonomy_vocabulary', 'label' => t('created vocabulary')),
      ),
      'access callback' => 'rules_taxonomy_vocabulary_integration_access',
    ),
    'taxonomy_vocabulary_update' => array(
      'label' => t('After updating an existing vocabulary'),
      'group' => t('Taxonomy'),
      'variables' => array(
        'vocabulary' => array('type' => 'taxonomy_vocabulary', 'label' => t('updated vocabulary')),
      ),
      'access callback' => 'rules_taxonomy_vocabulary_integration_access',
    ),
    'taxonomy_vocabulary_delete' => array(
      'label' => t('After deleting a vocabulary'),
      'group' => t('Taxonomy'),
      'variables' => array(
        'vocabulary' => array('type' => 'taxonomy_vocabulary', 'label' => t('deleted vocabulary')),
      ),
      'access callback' => 'rules_taxonomy_vocabulary_integration_access',
    ),
  );
}

/**
 * Taxonomy term integration access callback.
 */
function rules_taxonomy_term_integration_access($type, $name) {
  if ($type == 'event' || $type == 'condition') {
    return entity_metadata_entity_access('view', 'taxonomy_term');
  }
}

/**
 * Taxonomy vocabulary integration access callback.
 */
function rules_taxonomy_vocabulary_integration_access($type, $name) {
  if ($type == 'event' || $type == 'condition') {
    return entity_metadata_entity_access('view', 'taxonomy_vocabulary');
  }
}

/**
 * Implements hook_rules_data_info() on behalf of the taxonomy module.
 * @see rules_core_modules()
 */
function rules_taxonomy_data_info() {
  return array(
    'taxonomy_term' => array(
      'label' => t('taxonomy term'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataCRUDInterface' => array('class' => 'RulesDataEntityExtender')
      ),
    ),
    'taxonomy_vocabulary' => array(
      'label' => t('taxonomy vocabulary'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataCRUDInterface' => array('class' => 'RulesDataEntityExtender')
      ),
    ),
  );
}

/**
 * @}
 */
