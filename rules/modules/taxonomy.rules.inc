<?php
// $Id$

/**
 * @file
 * Rules integration for the taxonomy module.
 */


/**
 * Implementation of hook_rules_event_info().
 * @ingroup rules
 */
function taxonomy_rules_event_info() {
  return array(
    'taxonomy_term_insert' => array(
      'label' => t('After saving a new term'),
      'module' => 'Taxonomy',
      'arguments' => rules_events_hook_taxonomy_term_arguments(t('created term'))
      ),
    'taxonomy_term_update' => array(
      'label' => t('After updating a term'),
      'module' => 'Taxonomy',
      'arguments' => rules_events_hook_taxonomy_term_arguments(t('updated term'), TRUE)
      ),
  );
}

/**
 * Returns some arguments suitable for using it with a term
 */
function rules_events_hook_taxonomy_term_arguments($term_label, $update = FALSE) {
  $args = array(
    'term' => array(
      'type' => 'taxonomy_term',
      'label' => $term_label,
    ),
  );
  if ($update) {
    $args += array(
      'term_unchanged' => array(
        'type' => 'taxonomy_term',
        'label' => t('unchanged term'),
        'handler' => 'rules_events_argument_taxonomy_term_unchanged',
      ),
    );
  }
  return $args + rules_events_global_user_argument();
}

/**
 * Gets the term object, that doesn't contain the modified changes
 */
function rules_events_argument_taxonomy_term_unchanged($taxonomy_term) {
  return $taxonomy_term->tid ? taxonomy_get_term($taxonomy_term->tid) : $taxonomy_term;
}

/**
 * Implementation of hook_rules_action_info().
 */
function taxonomy_rules_action_info() {
  $info = array();
  $info['rules_action_taxonomy_load_term'] = array(
    'label' => t('Load a term'),
    'new variables' => array(
      'taxonomy_term' => array(
        'type' => 'taxonomy_term',
        'label' => t('Taxonomy term'),
      ),
    ),
    'eval input' => array('term_text'),
    'help' => t('Loading a taxonomy term will allow you to act on this term, for example you will be able to assign this term to a content.'),
    'module' => 'Taxonomy',
  );
  $info['rules_action_taxonomy_term_assign_to_content'] = array(
    'label' => t('Assign a term to content'),
    'arguments' => array(
      'node' => array(
        'type' => 'node',
        'label' => t('Content which term will be assigned to'),
      ),
      'taxonomy_term' => array(
        'type' => 'taxonomy_term',
        'label' => t('Taxonomy term'),
      ),
    ),
    'module' => 'Taxonomy',
  );
  $info['rules_action_taxonomy_term_remove_from_content'] = array(
    'label' => t('Remove a term from content'),
    'arguments' => array(
      'node' => array(
        'type' => 'node',
        'label' => t('Content which term will be removed from'),
      ),
      'taxonomy_term' => array(
        'type' => 'taxonomy_term',
        'label' => t('Taxonomy term'),
      ),
    ),
    'module' => 'Taxonomy',
  );
  return $info;
}

/**
 * Action: Load a term.
 */
function rules_action_taxonomy_load_term($settings) {
  if ($term = taxonomy_get_term(!empty($settings['term']['term_text']) ? $settings['term']['term_text'] : $settings['term']['term_select'])) {
    return array('taxonomy_term' => $term);
  }
}

/**
 * Action: Assign or remove a term to content.
 */
function rules_action_taxonomy_term_assign_to_content($node, $taxonomy_term, $settings) {
  if (!isset($node->taxonomy[$taxonomy_term->tid])) {
    $node->taxonomy[$taxonomy_term->tid] = $taxonomy_term;
    return array('node' => $node);
  }
}

/**
 * Action: Remove a term from content.
 */
function rules_action_taxonomy_term_remove_from_content($node, $taxonomy_term, $settings) {
  if (isset($node->taxonomy[$taxonomy_term->tid])) {
    unset($node->taxonomy[$taxonomy_term->tid]);
    return array('node' => $node);
  }
}

/**
 * Implementation of hook_rules_data_type_info().
 */
function taxonomy_rules_data_type_info() {
  return array(
    'taxonomy_term' => array(
      'label' => t('Taxonomy term'),
      'class' => 'rules_data_type_taxonomy_term',
      'savable' => FALSE,
      'identifiable' => TRUE,
      'module' => 'Taxonomy',
    ),
  );
}

/**
 * Defines the taxonomy term data type.
 */
class rules_data_type_taxonomy_term extends rules_data_type {

  function load($tid) {
    return taxonomy_get_term($tid);
  }

  function get_identifier() {
    $term = &$this->get();
    return $term->tid;
  }
}
