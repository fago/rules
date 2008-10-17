<?php
// $Id$

/**
 * @file
 * Rules configuration forms for the taxonomy module.
 */

/**
 * Action: Load a term configuration form.
 *
 * Multistep form.
 */
function rules_action_taxonomy_load_term_form($settings, &$form, $form_state) {
  $settings += array('vocabulary' => 0);
  if (empty($settings['vocabulary'])) {
    // Get existing taxonomy vocabularies.
    $vocab = $options = array();
    $vocabs = taxonomy_get_vocabularies();
    foreach ($vocabs as $vocab) {
      $options[$vocab->vid] = check_plain($vocab->name);
    }

    $form['settings']['vocabulary'] = array(
      '#type' => 'select',
      '#title' => t('Vocabulary'),
      '#options' => $options,
      '#description' => !empty($options) ? t('Select the vocabulary.') : t('There are no existing vocabularies, you should !add one.', array('!add' => l(t('add'), '/admin/content/taxonomy/add/vocabulary'))),
      '#required' => TRUE,
      '#disabled' => empty($options),
    );
    // Hide some form elements in the first step.
    $form['new']['#access'] = FALSE;
    $form['input_help']['#access'] = FALSE;
    $form['weight']['#access'] = FALSE;

    // Replace the usual submit handlers with our own handler.
    $form['submit']['#submit'] = array('rules_action_taxonomy_load_term_form_step_submit');
    $form['submit']['#value'] = t('Continue');
  }
  else {
    $options = array();
    $vocabulary = taxonomy_vocabulary_load($settings['vocabulary']);
    $terms = taxonomy_get_tree($vocabulary->vid);
    foreach ($terms as $term) {
      $options[$term->tid] = check_plain($term->name);
    }
    $form['settings']['term'] = array(
      '#type' => 'fieldset',
      '#title' => t("!vocab's terms", array('!vocab' => $vocabulary->name)),
      '#description' => empty($options) ? t('There are no terms in the vocabulary, you should !add one.', array('!add' => l(t('add'), 'admin/content/taxonomy/'. $vocabulary->vid .'/add/term' ))) : t('Select an existing term or manually enter the name of the term that should be added or removed from the content.'),
    );
    $form['settings']['term']['term_select'] = rules_taxonomy_form($vocabulary->vid, !empty($settings['term']['term_select']) ? $settings['term']['term_select'] : 0);

    $form['settings']['term']['term_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Select by term id'),
      '#default_value' => !empty($settings['term']['term_text']) ? $settings['term']['term_text'] : '',
      '#disabled' => empty($options),
      '#description' => t('Optional: enter the term id (<em>not the term name</em>) that should be loaded . If this field is used "Select a term" field will be ignored.'),
    );
  }
}

function rules_action_taxonomy_load_term_form_step_submit($form, &$form_state) {
  rules_admin_save_element_label($form, $form_state, $form_state['element']);
  $form_state['element']['#settings']['vocabulary'] = $form_state['values']['settings']['vocabulary'];
}

/**
 * Own version of taxonomy_form(), which forces the form to be not multiple.
 */
function rules_taxonomy_form($vid, $value = 0, $help = NULL, $name = 'taxonomy') {
  $vocabulary = taxonomy_vocabulary_load($vid);
  $help = ($help) ? $help : $vocabulary->help;
  $blank = ($vocabulary->required) ? t('- Please choose -') : t('- None selected -');
  return _taxonomy_term_select(check_plain($vocabulary->name), $name, $value, $vid, $help, FALSE, $blank);
}

