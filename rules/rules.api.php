<?php
// $Id$

/**
 * @file
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */


/**
 * @defgroup rules Rules module integrations.
 *
 * Module integrations with the rules module.
 *
 * The Rules developer documentation describes how modules can integrate with
 * rules: http://drupal.org/node/298486.
 */

/**
 * @defgroup rules_hooks Rules' hooks
 * @{
 * Hooks that can be implemented by other modules in order to extend rules.
 */


/**
 * Define rules compatible actions.
 *
 * This hook is required in order to add a new rules action. It should be
 * placed in the file MODULENAME.rules.inc.
 *
 * @return
 *   An array of information on the module's provided rules actions. The array
 *   contains a sub-array for each action, with the action name as the key.
 *
 *   Possible attributes for each sub-array are:
 *
 *    - 'label'
 *         The label of the action. Start capitalized. Required.
 *    - 'module'
 *         The providing module's user readable name. Used for grouping the
 *         actions in the interface. Should start with a capital letter.
 *         Required.
 *    - 'arguments'
 *         An array describing the arguments needed by the action with the
 *         argument's name as key. Optional. Each argument has to be described
 *         by a sub-array with possible attributes as described afterwards.
 *   - 'new variables'
 *         An array describing the new variables the action adds to the rules
 *         evaluation state with the variable name as key. Optional. Each
 *         variable has to be described by a sub-array with possible
 *         attributes as described afterwards.
 *   - 'eval input'
 *         An array containg form element names of elements contained in the
 *         actions settings form ($form['settings']) to which input evaluators
 *         should be attached. Optional.
 *   - 'label callback'
 *         A callback to improve the action's label once it has been configured.
 *         Optional (Defaults to {ACTION_NAME}_label).
 *         @see rules_action_callback_label().
 *   - 'base'
 *         The base for action implementation callbacks to use instead of the
 *         action's name. This is useful for having a single implementation for
 *         a couple of (probably somehow similar) actions. Optional (defaults
 *         to the name).
 *   - 'help'
 *         A help text to assist the user during action configuration. Optional.
 *
 *
 *  Each 'arguments' array may contain the following properties:
 *   -  'label'
 *         The label of the argument. Start capitalized. Required.
 *   - 'type'
 *         The rules data type of the variable, which is to be passed to
 *         the action. See http://drupal.org/node/298633 for a list of
 *         known types. Required.
 *   - 'description'
 *         If necessary, further description of the argument. The usage
 *         of this property depends on the data type. Optional.
 *   - 'default value'
 *         The value to pass to the action, when there is no specified
 *         value. Optional.
 *         It's main usage is in conjunction with the data type 'value'
 *         to pass some information from this hook to an actions base
 *         implementation.
 *
 *  Each 'new variables' array may contain the following properties:
 *   - 'label'
 *         The default label of the new variable. Start capitalized.
 *         Required.
 *   - 'type'
 *         The rules data type of the variable. See
 *         http://drupal.org/node/298633 for a list of known types.
 *         Required.
 *   - 'save'
 *         If this is set to TRUE, the new variable is saved by rules
 *         when the rules evaluation ends. Only possible for data types
 *         marked as 'savable'. Optional (defaults to FALSE).
 *   - 'label callback'
 *         A callback to improve the variables label using the action's
 *         configuration settings. Optional.
 *
 *  The module has to provide an implementation for each action, for which the
 *  function name has to equal the action's name or if specified, the action's
 *  base property. The other callbacks are optional.
 *
 *  @see rules_action_callback(), rules_action_callback_form(), rules_action_callback_validate(),
 *    rules_action_callback_submit(), rules_action_callback_help().
 */
function hook_rules_action_info() {
  return array(
    'rules_action_mail_to_user' => array(
      'label' => t('Send a mail to a user'),
      'arguments' => array(
        'user' => array('type' => 'user', 'label' => t('Recipient')),
      ),
      'module' => 'System',
      'eval input' => array('subject', 'message', 'from'),
    ),
  );
}


/**
 * The implementation callback for an action. It should be
 * placed in the file MODULENAME.rules.inc.
 *
 * @param
 *   The callback gets the arguments passed as described in
 *   hook_rules_action_info() as well as an array containing
 *   the action's configuration settings, if there are any.
 *
 * @return
 *   The action may return an array containg variables and their
 *   names as key.
 *   This is used to let rules save a variable having a savable
 *   data type. Or also, if the action has specified to provide new
 *   variables it can do so by returning the variables. For
 *   an example adding a new variable see rules_action_load_node().
 *
 *   Conditions have to return a boolean value.
 *
 * @see hook_rules_action_info().
 */
function rules_action_callback($node, $title, $settings) {
  $node->title = $title;
  return array('node' => $node);
}

/**
 * The configuration form callback for an action.
 * It should be placed in the file MODULENAME.rules_forms.inc or in
 * MODULENAME.rules.inc.
 *
 * This callback can be used to alter the automatically generated
 * configuration form. New form elements should be put in $form['settings']
 * as its form values are used to populate $settings automatically. If some
 * postprocessing of the values is necessary the action may implement
 * rules_action_callback_submit().
 *
 * @param $settings
 *   The array of configuration settings to edit. This array is going to be
 *   passed to the action implementation once executed.
 * @param $form
 *   The configuration form as generated by rules. The modify it, has to be
 *   taken by reference. Additional form elements should go into
 *   $form['settings']. To let rules know about additional textual form
 *   elements use the 'eval input' property of hook_rules_action_info() so
 *   rules adds input evaluation support to them.
 * @param $form_state
 *   The form's form state.
 *
 *
 * @see rules_action_callback_validate(), rules_action_callback_submit()
 *
 */
function rules_action_callback_form($settings, &$form) {
  $settings += array('type' => array());
  $form['settings']['type'] = array(
    '#type' => 'select',
    '#title' => t('Content types'),
    '#options' => node_get_types('names'),
    '#multiple' => TRUE,
    '#default_value' => $settings['type'],
    '#required' => TRUE,
  );
}

/**
 * The configuration form validation callback for an action.
 * It should be placed in the file MODULENAME.rules_forms.inc or in
 * MODULENAME.rules.inc.
 *
 * This callback can be implemented to validate the action's configuration
 * form.
 *
 * @param $form
 *   The configuration form.
 * @param $form_state
 *   The form's form state.
 *
 *
 * @see rules_action_callback_form(), rules_action_callback_submit()
 */
function rules_action_callback_validate($form, $form_state) {
  if (!$form_state['values']['settings']['username'] && !$form_state['values']['settings']['userid']) {
    form_set_error('username', t('You have to enter the user name or the user id.'));
  }
}

/**
 * The configuration form submit callback for an action.
 * It should be placed in the file MODULENAME.rules_forms.inc or in
 * MODULENAME.rules.inc.
 *
 * This callback can be implemented to post process the action's
 * configuration form values before they are stored.
 *
 * @param $settings
 *   The configuration settings to store.
 * @param $form
 *   The configuration form.
 * @param $form_state
 *   The form's form state.
 *
 *
 * @see rules_action_callback_validate(), rules_action_callback_submit()
 */
function rules_action_callback_submit(&$settings, $form, $form_state) {
  $settings['roles'] = array_filter(array_keys(array_filter($settings['roles'])));
}









/**
 * Define rules conditions.
 *
 * This hook is required in order to add a new rules condition. It should be
 * placed in the file MODULENAME.rules.inc.
 *
 * Adding conditions works exactly the same way as adding actions, with the
 * exception that conditions can't add new variables. Thus the 'new variables'
 * property is not supported. Furthermore the condition implementation callback
 * has to return a boolean value.
 *
 * @see hook_rules_action_info().
 */
function hook_rules_condition_info() {
  return array(
    'rules_condition_text_compare' => array(
      'label' => t('Textual comparison'),
      'arguments' => array(
        'text1' => array('label' => t('Text 1'), 'type' => 'string'),
        'text2' => array('label' => t('Text 2'), 'type' => 'string'),
      ),
      'help' => t('TRUE is returned, if both texts are equal.'),
      'module' => 'Rules',
    ),
  );
}

/**
 * @}
 */

