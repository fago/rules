<?php
// $Id$

/**
 * @file Rules UI forms
 */

/**
 * Ajax callback for reloading the whole form.
 */
function rules_ui_form_ajax_reload_form($form, $form_state) {
  return $form;
}

/**
 * Defines #ajax properties.
 */
function rules_ui_form_default_ajax($effect = 'slide') {
  return array(
    'callback' => 'rules_ui_form_ajax_reload_form',
    'wrapper' => 'rules-form-wrapper',
    'effect' => $effect,
    'speed' => 'fast',
  );
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
 * General form submit handler, that rebuilds the form
 */
function rules_form_submit_rebuild($form, &$form_state) {
  $form_state['rebuild'] = TRUE;
}


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
  // By default, rebuild instead of redirect as this is faster.
  if (empty($form_state['redirect'])) {
    $form_state['rebuild'] = TRUE;
  }
  // We need to clear the values so the new weight-id combinations can take
  // affect on rebuild.
  $form_state['input'] = array();
}

/**
 * Gets the confirmation question for valid operations, or else FALSE.
 */
function rules_ui_confirm_operations($op, $rules_config) {
  switch ($op) {
    case 'enable':
      return t('Are you sure you want to enable the %plugin %label?', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label()));
    case 'disable':
      return t('Are you sure you want to disable the %plugin %label?', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label()));
    case 'revert':
      return t('Are you sure you want to revert the %plugin %label?', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label()));
    case 'delete':
      return t('Are you sure you want to delete the %plugin %label?', array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label()));
    default:
      return FALSE;
  }
}

/**
 * Confirmation form for applying the operation to the config.
 */
function rules_ui_form_rules_config_confirm_op($form, &$form_state, $rules_config, $op) {
  if ($confirm_question = rules_ui_confirm_operations($op, $rules_config)) {
    $form_state += array('rules_config' => $rules_config, 'op' => $op);
    return confirm_form($form, $confirm_question, RULES_UI_PATH, t('This action cannot be undone.'), t('Confirm'), t('Cancel'));
  }
  else {
    drupal_not_found();
    exit;
  }
}

/**
 * Applies the operation and returns the message to show to the user. Also the
 * operation is logged to the watchdog. Note that the string is defined two
 * times so that the translation extractor can find it.
 */
function rules_ui_confirm_operation_apply($op, $rules_config) {
  $vars = array('%plugin' => $rules_config->plugin(), '%label' => $rules_config->label());
  $edit_link = l(t('edit'), RulesPluginUI::path($rules_config->name));

  switch ($op) {
    case 'enable':
      $rules_config->active = TRUE;
      $rules_config->save();
      watchdog('rules', 'Enabled %plugin %label.', $vars, WATCHDOG_NOTICE, $edit_link);
      return t('Enabled %plugin %label.', $vars);

    case 'disable':
      $rules_config->active = FALSE;
      $rules_config->save();
      watchdog('rules', 'Disabled %plugin %label.', $vars, WATCHDOG_NOTICE, $edit_link);
      return t('Disabled %plugin %label.', $vars);

    case 'revert':
      $rules_config->delete();
      watchdog('rules', 'Reverted %plugin %label to the defaults.', $vars, WATCHDOG_NOTICE, $edit_link);
      return t('Reverted %plugin %label to the defaults.', $vars);

    case 'delete':
      $rules_config->delete();
      watchdog('rules', 'Deleted %plugin %label.', $vars);
      return t('Deleted %plugin %label.', $vars);
  }
}

/**
 * Rule config deletion form submit callback.
 */
function rules_ui_form_rules_config_confirm_op_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    $msg = rules_ui_confirm_operation_apply($form_state['op'], $form_state['rules_config']);
    drupal_set_message($msg);
  }
}

/**
 * Add a new element a rules configuration.
 */
function rules_ui_add_element($form, &$form_state, $rules_config, $plugin_name, RulesContainerPlugin $parent) {
  $cache = rules_get_cache();
  if (!isset($cache['plugin_info'][$plugin_name]['class'])) {
    drupal_not_found();
    exit;
  }
  $plugin_is_abstract = in_array('RulesAbstractPlugin', class_parents($cache['plugin_info'][$plugin_name]['class']));
  // In the first step create the element and in the second step show its edit
  // form.
  if ($plugin_is_abstract && !isset($form_state['rules_element'])) {
    RulesPluginUI::formDefaults($form);
    $form_state += array('parent_element' => $parent, 'plugin' => $plugin_name);

    $form['element_name'] = array(
      '#type' => 'select',
      '#title' => t('Select the %element to add', array('%element' => $plugin_name)),
      '#options' => RulesPluginUI::getOptions($plugin_name . '_info'),
      '#ajax' => rules_ui_form_default_ajax() + array(
        'trigger_as' => array('name' => 'continue'),
      ),
    );
    $form['continue'] = array(
      '#type' => 'submit',
      '#name' => 'continue',
      '#value' => t('Continue'),
      '#ajax' => rules_ui_form_default_ajax(),
    );
  }
  elseif (!$plugin_is_abstract) {
    // Create the initial, empty element.
    $element = rules_plugin_factory($plugin_name);
    // Always add the new element at the bottom, thus set an appropriate weight.
    $iterator = $parent->getIterator();
    if ($sibling = end($iterator)) {
      $element->weight = $sibling->weight + 1;
    }
    $element->setParent($parent);
    $form_state['rules_element'] = $element;
  }

  if (isset($form_state['rules_element'])) {
    $form_state['rules_element']->form($form, $form_state, array('button' => TRUE, 'init' => TRUE));
    $form['#validate'][] = 'rules_ui_edit_element_validate';
    $form['#submit'][] = 'rules_ui_edit_element_submit';
  }
  return $form;
}

/**
 * Add element submit callback.
 * Used for "abstract plugins" to create the initial element object with the
 * given implemenation name and rebuild the form.
 */
function rules_ui_add_element_submit($form, &$form_state) {
  $element = rules_plugin_factory($form_state['plugin'], $form_state['values']['element_name']);

  // Clear the element settings so they won't be processed on serialization as
  // there is nothing to be processed yet.
  $element->settings = array();
  $element->setParent($form_state['parent_element']);

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
