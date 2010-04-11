<?php
// $Id$

/**
 * @file Rules UI forms
 */

/**
 * Edit a rules configuration.
 */
function rules_ui_form_edit_rules_config($form, &$form_state, $rules_config) {
  $form_state += array('rules_element' => $rules_config);
  // Add the rule configuration's form.
  $rules_config->form($form, $form_state, array('show settings' => TRUE, 'button' => TRUE));
  return $form;
}

/**
 * Edit a rules configuration form validation callback.
 */
function rules_ui_form_edit_rules_config_validate($form, &$form_state) {
  $form_state['rules_element']->form_validate($form, $form_state);
}

/**
 * Edit a rules configuration form submit callback.
 */
function rules_ui_form_edit_rules_config_submit($form, &$form_state) {
  $form_state['rules_element']->form_submit($form, $form_state);
  // Rebuild instead of redirect as this is faster.
  $form_state['rebuild'] = TRUE;
  // We need to clear the values so the new weight-id combinations can take
  // affect on rebuild.
  $form_state['input'] = array();
}

/**
 * Form for deleting rule config.
 */
function rules_ui_form_delete_rules_config($form, &$form_state, $rules_config) {
  $form_state += array('rules_config' => $rules_config);
  $confirm_question = t('Are you sure you want to delete the %plugin %label?', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label()));
  return confirm_form($form, $confirm_question, RULES_UI_PATH, t('This action cannot be undone.'), t('Delete'), t('Cancel'));
}

/**
 * Rule config deletion form submit callback.
 */
function rules_ui_form_delete_rules_config_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    $rules_config = $form_state['rules_config'];
    $rules_config->delete();
    watchdog('content', 'Deleted rules configuration %plugin %label.', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label()));
    drupal_set_message(t('Deleted rules configuration %plugin %label.', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label())));
  }
}

/**
 * Add a new element a rules configuration.
 */
function rules_ui_add_element($form, &$form_state, $rules_config, $type, $element_id) {
  $form_state += array('rules_config' => $rules_config, 'type' => $type, 'element_id' => $element_id);
  // In the first step create the element and in the second step show its edit
  // form.
  if (!isset($form_state['rules_element'])) {
    $form['element_name'] = array(
      '#type' => 'select',
      '#title' => t('Select the %element to add', array('%element' => $type)),
      '#options' => RulesPluginUI::getOptions($type . '_info'),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Continue'),
    );
  }
  else {
    $form_state['rules_element']->form($form, $form_state, array('button' => TRUE));
    $form['#validate'][] = 'rules_ui_edit_element_validate';
    $form['#submit'][] = 'rules_ui_edit_element_submit';
  }
  return $form;
}

/**
 * Add element submit callback.
 * Create the new element and rebuild the form.
 */
function rules_ui_add_element_submit($form, &$form_state) {
  $rules_config = $form_state['rules_config'];
  $parent = RulesPluginUI::getChildElementById($rules_config, $form_state['element_id']);
  $element = rules_plugin_factory($form_state['type'], $form_state['values']['element_name']);

  // Clear the element settings so they won't be processed on serialization as
  // there is nothing to be processed yet.
  $element->settings = array();

  if ($element instanceof RulesConditionInterface) {
    $parent->condition($element);
  }
  else {
    $parent->action($element);
  }
  $form_state['rules_element'] = $element;
  $form_state['rebuild'] = TRUE;
}

/**
 * Delete elements.
 */
function rules_ui_delete_element($form, &$form_state, $rules_config, $rules_element) {
  $form_state += array('rules_config' => $rules_config, 'rules_element' => $rules_element);
  $confirm_question = t('Are you sure you want to delete the %element_plugin %element_name?', array('%element_plugin' => $rules_element->plugin(), '%element_name' => $rules_element->label(), '%plugin' => $rules_config->plugin(), '%label' => $rules_config->label()));
  return confirm_form($form, $confirm_question, RULES_UI_PATH, t('This action cannot be undone.'), t('Delete'), t('Cancel'));
}

/**
 * Rule config deletion form validation callback.
 */
function rules_ui_delete_element_validate($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    $form_state['rules_element']->delete();
    try {
      $form_state['rules_config']->integrityCheck();
    }
    catch (RulesException $e) {
      //TODO: Add visual feedback which element had troubles.
      form_set_error('', $e->getMessage());
    }
  }
}

/**
 * Rule config deletion form submit callback.
 */
function rules_ui_delete_element_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    $rules_config = $form_state['rules_config'];
    $rules_config->save();
  }
}


/**
 * Configure a rule element.
 */
function rules_ui_edit_element($form, &$form_state, $rules_config, $element) {
  $form_state += array('rules_element' => $element);
  $form_state['rules_element']->form($form, $form_state, array('button' => TRUE));
  return $form;
}

/**
 * Validate the element configuration.
 */
function rules_ui_edit_element_validate($form, &$form_state) {
  $form_state['rules_element']->form_validate($form, $form_state);
}

/**
 * Submit the element configuration.
 */
function rules_ui_edit_element_submit($form, &$form_state) {
  $form_state['rules_element']->form_submit($form, $form_state);
  $config_name = $form_state['rules_element']->root()->name;
  $form_state['redirect'] = RULES_UI_RULE_PATH . '/' . $config_name;
}

/**
 * Submit handler for switching the parameter input mode.
 */
function rules_ui_parameter_replace_submit($form, &$form_state) {
  if (isset($form_state['triggering_element'])) {
    $name = $form_state['triggering_element']['#parameter'];
    $form_state['parameter_mode'][$name] = $form_state['parameter_mode'][$name] == 'selector' ? 'input' : 'selector';
  }
  $form_state['rebuild'] = TRUE;
}

/**
 * Ajax callback replacing the parameter form.
 */
function rules_ui_parameter_replace($form, $form_state) {
  return $form['parameter'];
}

/**
 * Add a new event.
 */
function rules_ui_add_event($form, &$form_state, $rules_config) {
  $form_state += array('rules_config' => $rules_config);
  $form['event'] = array(
    '#type' => 'select',
    '#title' => t('React on event'),
    '#options' => RulesPluginUI::getOptions('event_info'),
    '#description' => t('Specify the event you want to react on.'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  $form_state['redirect'] = RulesPluginUI::path($rules_config->name);
  return $form;
}

/**
 * Validate the event configuration.
 */
function rules_ui_add_event_validate($form, &$form_state) {
  if (!empty($form_state['values']['event'])) {
    $rules_config = $form_state['rules_config'];
    $rules_config->event($form_state['values']['event']);
    $rules_config->form_validate($form, $form_state);
  }
}

/**
 * Submit the event configuration.
 */
function rules_ui_add_event_submit($form, &$form_state) {
  if (!empty($form_state['values']['event'])) {
    $rules_config = $form_state['rules_config'];
    $rules_config->save();
    drupal_set_message(t('Add event to rule %label', array('%label' => $rules_config->label())));
  }
}

/**
 * Form to remove a event from a rule.
 */
function rules_ui_remove_event($form, &$form_state, $rules_config, $event) {
  $form_state += array('rules_config' => $rules_config, 'rules_event' => $event);
  $confirm_question = t('Are you sure you want to remove %event from the %plugin %label?', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label(), '%event' => $event));
  return confirm_form($form, $confirm_question, RULES_UI_RULE_PATH, t('This action cannot be undone.'), t('Remove'), t('Cancel'));
}

/**
 * Validate the event configuration.
 */
function rules_ui_remove_event_validate($form, &$form_state) {
  $rules_config = $form_state['rules_config'];
  $rules_config->removeEvent($form_state['rules_event']);
  try {
    $rules_config->integrityCheck();
  }
  catch (RulesException $e) {
    //TODO: Add visual feedback which element had troubles.
    form_set_error('', $e->getMessage());
  }
}

/**
 * Submit the event configuration.
 */
function rules_ui_remove_event_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    $rules_config = $form_state['rules_config'];
    $rules_config->save();
    $form_state['redirect'] = RulesPluginUI::path($rules_config->name);
    drupal_set_message(t('Event %event was removed from %plugin %label', array('%event' => $form_state['rules_event'], '%label' => $rules_config->label(), '%plugin' => $rules_config->plugin())));
  }
}

/**
 * FAPI validation of an integer element. Copy of the private function
 * _element_validate_integer().
 */
function rules_ui_element_integer_validate($element, &$form_state) {;
  $value = $element['#value'];
  if ($value !== '' && (!is_numeric($value) || intval($value) != $value)) {
    form_error($element, t('%name must be an integer.', array('%name' => $element['#title'])));
  }
}

/**
 * FAPI validation of a decimal element. Improved version of the private
 * function _element_validate_number().
 */
function rules_ui_element_decimal_validate($element, &$form_state) {
  // Substitute the decimal separator ",".
  $value = strtr($element['#value'], ',', '.');
  if ($value != '' && !is_numeric($value)) {
    form_error($element, t('%name must be a number.', array('%name' => $element['#title'])));
  }
  elseif ($value != $element['#value']) {
    form_set_value($element, $value, $form_state);
  }
}

/**
 * FAPI validation of a date element. Makes sure the specified date format is
 * correct and converts date values specifiy a fixed (= non relative) date to
 * a timestamp. Relative dates are handled by the date input evaluator.
 */
function rules_ui_element_date_validate($element, &$form_state) {
  $value = $element['#value'];
  if ($value == '' || (is_numeric($value) && intval($value) == $value)) {
    // The value is a timestamp.
    return;
  }
  elseif (is_string($value) && RulesDateInputEvaluator::gmstrtotime($value) === FALSE) {
    form_error($element, t('Wrong date format. Specify the date in the format %format.', array('%format' => gmdate('Y-m-d H:i:s', time() + 86400))));
  }
  elseif (is_string($value) && RulesDateInputEvaluator::isFixedDateString($value)) {
    // As the date string specifies a fixed format, we can convert it now.
    $value = RulesDateInputEvaluator::gmstrtotime($value);
    form_set_value($element, $value, $form_state);
  }
}

/**
 * FAPI process callback for the duration element type.
 */
function rules_ui_element_duration_process($element, &$form_state) {
  $element['value'] = array(
    '#type' => 'textfield',
    '#size' => 8,
    '#element_validate' => array('rules_ui_element_integer_validate'),
    '#default_value' => $element['#default_value'],
    '#required' => !empty($element['#required']),
  );
  $element['multiplier'] = array(
    '#type' => 'select',
    '#options' => rules_ui_element_duration_multipliers(),
    '#default_value' => 1,
  );

  // Put the child elements in a container-inline div.
  $element['value']['#prefix'] = '<div class="rules-duration container-inline">';
  $element['multiplier']['#suffix'] = '</div>';

  // Set an appropriate multiplier.
  foreach (array_keys(rules_ui_element_duration_multipliers()) as $m) {
    if ($element['value']['#default_value'] % $m == 0) {
      $element['multiplier']['#default_value'] = $m;
    }
  }
  // Divide value by the multiplier, so the display is correct.
  $element['value']['#default_value'] /= $element['multiplier']['#default_value'];
  return $element;
}

/**
 * Defines possible duration multiplier.
 */
function rules_ui_element_duration_multipliers() {
  return array(
    1 => t('seconds'),
    60 => t('minutes'),
    3600 => t('hours'),
    86400 => t('days'),
  );
}

/**
 * Helper function to determine the value for a rules duration form
 * element.
 */
function rules_ui_element_duration_value($element, $input = FALSE) {
  // This runs before child elements are processed, so we cannot calculate the
  // value here. But we have to make sure the value is an array, so the form
  // API is able to proccess the children to set their values in the array. Thus
  // once the form API has finished processing the element, the value is an
  // array containing the child element values. Then finally the after build
  // callback converts it back to the numeric value and sets that.
  return array();
}

/**
 * FAPI after build callback for the duration parameter type form.
 * Fixes up the form value by applying the multiplier.
 */
function rules_ui_element_duration_after_build($element, &$form_state) {
  $element['#value'] = $element['value']['#value'] * $element['multiplier']['#value'];
  form_set_value($element, $element['#value'], $form_state);
  return $element;
}
