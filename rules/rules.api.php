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
 * placed into the file MODULENAME.rules.inc, which gets automatically included
 * when the hook is invoked.
 *
 * @return
 *   An array of information about the module's provided rules actions.
 *   The array contains a sub-array for each action, with the action name as
 *   the key.
 *   Possible attributes for each sub-array are:
 *   - label: The label of the action. Start capitalized. Required.
 *   - group: A group for this element, used for grouping the actions in the
 *     interface. Should start with a capital letter and be translated.
 *     Required.
 *   - parameter: An array describing all parameter of the action with
 *     the parameter's name as key. Optional. Each parameter has to be
 *     described by a sub-array with possible attributes as described
 *     afterwards.
 *   - provides: An array describing the variables the action provides to the
 *     evaluation state with the variable name as key. Optional. Each variable
 *     has to be described by a sub-array with possible attributes as described
 *     afterwards.
 *   - base: The base for action implementation callbacks to use instead of the
 *     action's name. Optional (defaults to the name).
 *   - callbacks: An array which allows to set specific function callbacks for
 *     the action. The default for each callback is the actions base appended
 *     by '_' and the callback name.
 *   - help: A help text to assist the user during action configuration.
 *     Optional. As an alternative one can implement
 *     rules_action_callback_help().
 *   - 'access callback': An optional callback, which has to return whether the
 *     currently logged in user is allowed to configure this action. See
 *     rules_node_integration_access() for an example callback.
 *  Each 'parameter' array may contain the following properties:
 *   - label: The label of the parameter. Start capitalized. Required.
 *   - type: The rules data type of the parameter, which is to be passed to the
 *     action. All types declared in hook_rules_data_info() may be specified, as
 *     well as an array of possible types. Also lists and lists of a given type
 *     can be specified by using the notating list<integer> as introduced by
 *     the entity metadata module. The special keyword '*' can be used when all
 *     types should be allowed. Required.
 *   - bundles: Optionally, an array of bundle names. When the specified type is
 *     set to a single entity type, this may be used to restrict the allowed
 *     bundles.
 *   - description: If necessary, a further description of the parameter.
 *     Optional.
 *   - options list: Optionally, a callback that returns an array of possible
 *     values for this parameter. The callback has to return an array as used
 *     by hook_options_list(). For an example implementation see
 *     rules_data_action_type_options().
 *   - save: If this is set to TRUE, the parameter will be saved by rules when
 *     the rules evaluation ends. This is only supported for savable data
 *     types. If the action returns FALSE, saving is skipped.
 *   - optional: May be set to TRUE, when the parameter isn't required.
 *   - 'default value': The value to pass to the action, in case the parameter
 *     is optional and there is no specified value. Optional.
 *   - restriction: Restrict how the argument for this parameter may be
 *     provided. Supported values are 'selector' and 'input'. Optional.
 *   - sanitize: Allows parameters of type 'text' to demand an already sanitized
 *     argument. Optionally.
 *  Each 'provides' array may contain the following properties:
 *   - label: The label of the variable. Start capitalized. Required.
 *   - type: The rules data type of the variable. All types declared in
 *     hook_rules_data_info() may be specified. Types may be parametrized e.g.
 *     the types node<page> or list<integer> are valid.
 *   - save: If this is set to TRUE, the provided variable is saved by rules
 *     when the rules evaluation ends. Only possible for savable data types.
 *     Optional (defaults to FALSE).
 *   - 'label callback': A callback to improve the variables label using the
 *     action's configuration settings. Optional.
 *
 *  The module has to provide an implementation for each action, being a
 *  function named as specified in the 'base' key or for the execution callback.
 *  All other possible callbacks are optional.
 *  Supported action callbacks by rules are defined and documented in the
 *  RulesPluginImplInterface. However any module may extend the action plugin
 *  based upon a defined interface using hook_rules_plugin_info(). All methods
 *  defined in those interfaces can be overridden by the action implementation.
 *  The callback implementations for those interfaces may reside in any file
 *  specified in hook_rules_file_info().
 *
 *  @see hook_rules_file_info()
 *  @see rules_action_execution_callback()
 *  @see hook_rules_plugin_info()
 *  @see RulesPluginImplInterface
 */
function hook_rules_action_info() {
  return array(
    'mail_user' => array(
      'label' => t('Send a mail to a user'),
      'parameter' => array(
        'user' => array('type' => 'user', 'label' => t('Recipient')),
      ),
      'group' => t('System'),
      'base' => 'rules_action_mail_user',
      'callbacks' => array(
        'validate' => 'rules_action_custom_validation',
        'help' => 'rules_mail_help',
      ),
    ),
  );
}

/**
 * Specify files containing rules integration code.
 *
 * All files specified in that hook will be included when rules looks for
 * existing callbacks for any plugin. Rules remembers which callback is found in
 * which file and automatically includes the right file before it is executing
 * a plugin method callback. The file yourmodule.rules.inc is added by default
 * and need not be specified here.
 * This allows you to add new include files only containing functions serving as
 * plugin method callbacks in any file without having to care about file
 * inclusion.
 *
 * @return
 *   An array of file names without the file ending which defaults to '.inc'.
 */
function hook_rules_file_info() {
  return array('yourmodule.rules-eval');
}

/**
 * The execution callback for an action.
 *
 * It should be placed in any file included by your module or in a file
 * specified using hook_rules_file_info().
 *
 * @param
 *   The callback gets arguments passed as described as parameter in
 *   hook_rules_action_info() as well as an array containing the action's
 *   configuration settings.
 * @return
 *   The action may return an array containg parameter or provided variables
 *   with their names as key. This is used update the value of a parameter or to
 *   provdide the value for a provided variable.
 *   Apart from that any parameters which have the key 'save' set to TRUE will
 *   be remembered to be saved by rules unless the action returns FALSE.
 *   Conditions have to return a boolean value in any case.
 *
 * @see hook_rules_action_info()
 * @see hook_rules_file_info()
 */
function rules_action_execution_callback($node, $title, $settings) {
  $node->title = $title;
  return array('node' => $node);
}

/**
 * Define rules conditions.
 *
 * This hook is required in order to add a new rules condition. It should be
 * placed into the file MODULENAME.rules.inc, which gets automatically included
 * when the hook is invoked.
 *
 * Adding conditions works exactly the same way as adding actions, with the
 * exception that conditions can't provide variables and cannot save parameters.
 * Thus the 'provides' attribute is not supported. Furthermore the condition
 * implementation callback has to return a boolean value.
 *
 * @see hook_rules_action_info().
 */
function hook_rules_condition_info() {
  return array(
    'rules_condition_text_compare' => array(
      'label' => t('Textual comparison'),
      'parameter' => array(
        'text1' => array('label' => t('Text 1'), 'type' => 'text'),
        'text2' => array('label' => t('Text 2'), 'type' => 'text'),
      ),
      'help' => t('TRUE is returned, if both texts are equal.'),
      'group' => t('Rules'),
    ),
  );
}

/**
 * Define rules events.
 *
 * This hook is required in order to add a new rules event. It should be
 * placed into the file MODULENAME.rules.inc, which gets automatically included
 * when the hook is invoked.
 * The module has to invoke the event when it occurs using rules_invoke_event().
 * This function call has to happen outside of MODULENAME.rules.inc,
 * usually it's invoked directly from the providing module but wrapped by a
 * module_exists('rules') check.
 *
 * @return
 *   An array of information about the module's provided rules events. The array
 *   contains a sub-array for each event, with the event name as the key.
 *   Possible attributes for each sub-array are:
 *   - label: The label of the event. Start capitalized. Required.
 *   - group: A group for this element, used for grouping the events in the
 *     interface. Should start with a capital letter and be translated.
 *     Required.
 *   - 'access callback': An callback, which has to return whether the
 *     currently logged in user is allowed to configure rules for this event.
 *     Access should be only granted, if the user at least may access all the
 *     variables provided by the event. Optional.
 *   - help: A help text for rules reaction on this event.
 *   - variables: An array describing all variables that are available for
 *     elements reaction on this event. Optional. Each variable has to be
 *     described by a sub-array with the possible attributes:
 *     - label: The label of the variable. Start capitalized. Required.
 *     - type: The rules data type of the variable. All types declared in
 *       hook_rules_data_info() may be specified. Types may be parametrized e.g.
 *       the types node<page> or list<integer> are valid.
 *     - 'skip save': If the variable is saved after the event has occured
 *       anyway, set this to TRUE. So rules won't save the variable a second
 *       time. Optional, defaults to FALSE.
 *     - handler: A handler to load the actual variable value. This is useful
 *       for lazy loading variables. The handler gets all so far available
 *       variables passed in the order as defined. Optional. Also see
 *       http://drupal.org/node/298554.
 *
 *  @see rules_invoke_event()
 */
function hook_rules_event_info() {
  $items = array(
    'node_insert' => array(
      'label' => t('After saving new content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('created content')),
    ),
    'node_update' => array(
      'label' => t('After updating existing content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('updated content'), TRUE),
    ),
    'node_presave' => array(
      'label' => t('Content is going to be saved'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('saved content'), TRUE),
    ),
    'node_view' => array(
      'label' => t('Content is going to be viewed'),
      'group' => t('Node'),
      'help' => t("Note that if drupal's page cache is enabled, this event won't be generated for pages served from cache."),
      'variables' => rules_events_node_variables(t('viewed content')) + array(
        'build_mode' => array('type' => 'string', 'label' => t('view mode')),
      ),
    ),
    'node_delete' => array(
      'label' => t('After deleting content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('deleted content')),
    ),
  );
  // Specify that on presave the node is saved anyway.
  $items['node_presave']['variables']['node']['skip save'] = TRUE;
  return $items;
}

/**
 * Define rules data types.
 *
 * This hook is required in order to add a new rules data type. It should be
 * placed into the file MODULENAME.rules.inc, which gets automatically included
 * when the hook is invoked.
 * Rules builds upon the entity metadata module, thus to improve the support of
 * your data in rules, make it an entity if possible and provide metadata about
 * its properties and CRUD functions by integrating with the entity metadata
 * module.
 * For a list of data types defined by rules see rules_data_data_info().
 *
 *
 * @return
 *   An array of information about the module's provided data types. The array
 *   contains a sub-array for each data type, with the data type name as the key.
 *   Possible attributes for each sub-array are:
 *   - label: The label of the data type. Start uncapitalized. Required.
 *   - wrap: If set to TRUE, the data is wrapped internally using wrappers
 *     provided by the entity metadata module. This is required for entities and
 *     data structures to support the application of data selectors or
 *     intelligent saving.
 *   - data info: May be used for data structures being no entities to support
 *     data selectors via an entity metadata wrapper. Specify an array as
 *     expected by entity_metadata_wrapper(). Optionally.
 *   - parent: Optionally a parent type may be set to specify a sub-type
 *     relationship, which will be only used for checking compatible types. E.g.
 *     the 'entity' data type is parent of the 'node' data type, thus a node may
 *     be also used for any action needing an 'entity' parameter. Can be set to
 *     any known rules data type.
 *   - group: A group for this element, used for grouping the data types in the
 *     interface. Should start with a capital letter and be translated.
 *     Required.
 *   - 'token type': The type name as used by the token module. Defaults to the
 *     type name as used by rules. Use FALSE to let token ignore this type.
 *     Optional.
 *   - hidden: Whether the data type should be hidden from the UI. Optional
 *    (defaults to FALSE).
 *
 *  @see entity_metadata_wrapper()
 *  @see hook_rules_data_info_alter()
 *  @see rules_data_data_info()
 */
function hook_rules_data_info() {
  return array(
    'node' => array(
      'label' => t('content'),
      'parent' => 'entity',
      'group' => t('Node'),
    ),
  );
}

/**
 * Defines rules plugins.
 *
 * A rules configuration may consist of elements being instances of any rules
 * plugin. This hook can be used to define new or to extend rules plugins.
 *
 * @return
 *   An array of information about the module's provided rules plugins. The
 *   array contains a sub-array for each plugin, with the plugin name as the
 *   key. Possible attributes for each sub-array are:
 *   - label: A label for the plugin. Start capitalized. Required.
 *   - class: The implementation class. Has to extend the RulesPlugin class.
 *   - extenders: This allows one to specify faces extenders, which may be used
 *     to dynamically implement interfaces. Optional. All extenders specified
 *     here are setup automatically by rules once the object is created. To
 *     specify set this to an array, where the keys are the implemented
 *     interfaces pointing to another array with the keys:
 *     - class: The class of the extender, implementing the FacesExtender
 *       and the specified interface. Either 'class' or 'methods' has to exist.
 *     - methods: An array of callbacks that implement the methods of the
 *       interface where the method names are the keys and the callback names
 *       the values. There has to be a callback for each defined method.
 *     - file: An optional array describing the file to include when a method
 *       of the interface is invoked. The array entries known are 'type',
 *       'module', and 'name' matching the parameters of module_load_include().
 *       Only 'module' is required as 'type' defaults to 'inc' and 'name' to
 *       NULL.
 *   - overrides: An optional array, which may be used to specify callbacks to
 *     override specific methods. For that the following keys are supported:
 *     - methods: As in the extenders array, but you may specify as many methods
 *       here as you like.
 *     - file: Optionally an array specifying a file to include for a method.
 *       For each method appearing in methods a file may be specified by using
 *       the method name as key and another array as value, which describes the
 *       file to include - looking like the file array supported by 'extenders'.
 *
 *  @see class RulesPlugin
 *  @see hook_rules_plugin_info_alter()
 */
function hook_rules_plugin_info() {
  return array(
    'or' => array(
      'class' => 'RulesOr',
    ),
    'and' => array(
      'class' => 'RulesAnd',
    ),
    'rule' => array(
      'class' => 'Rule',
      'embeddable' => TRUE,
      'extenders' => array (
      // Interfaces => array( class => className / methods => array of Methods).
      ),
      'overrides' => array(
      // Array of overrides each being an array ('methods' => .., 'file' => ..).
      ),
    ),
  );
}

/**
 * Alter rules compatible actions.
 *
 * The implementation should be placed into the file MODULENAME.rules.inc, which
 * gets automatically included when the hook is invoked.
 *
 * @param $actions
 *   The items of all modules as returned from hook_rules_action_info().
 *
 * @see hook_rules_action_info().
 */
function hook_rules_action_info_alter(&$actions) {
  // The rules action is more powerful, so hide the core action
  unset($actions['rules_core_node_assign_owner_action']);
  // We prefer handling saving by rules - not by the user.
  unset($actions['rules_core_node_save_action']);
}

/**
 * Alter rules conditions.
 *
 * The implementation should be placed into the file MODULENAME.rules.inc, which
 * gets automatically included when the hook is invoked.
 *
 * @param $conditions
 *   The items of all modules as returned from hook_rules_condition_info().
 *
 * @see hook_rules_condition_info().
 */
function hook_rules_condition_info_alter(&$conditions) {
  // Change conditions
}

/**
 * Alter rules events.
 *
 * The implementation should be placed into the file MODULENAME.rules.inc, which
 * gets automatically included when the hook is invoked.
 *
 * @param $events
 *   The items of all modules as returned from hook_rules_event_info().
 *
 * @see hook_rules_event_info().
 */
function hook_rules_event_info_alter(&$events) {
  // Change events
}

/**
 * Alter rules data types.
 *
 * The implementation should be placed into the file MODULENAME.rules.inc, which
 * gets automatically included when the hook is invoked.
 *
 * @param $data_info
 *   The items of all modules as returned from hook_rules_data_info().
 *
 * @see hook_rules_data_info().
 */
function hook_rules_data_info_alter(&$data_info) {
  // Change data types
}

/**
 * Alter rules plugin info.
 *
 * The implementation should be placed into the file MODULENAME.rules.inc, which
 * gets automatically included when the hook is invoked.
 *
 * @param $plugin_info
 *   The items of all modules as returned from hook_rules_plugin_info().
 *
 * @see hook_rules_plugin_info().
 */
function hook_rules_plugin_info_alter(&$plugin_info) {
  // Change data types
}

/**
 * Act on rules configuration being loaded from the database.
 *
 * This hook is invoked during rules configuration loading, which is handled
 * by entity_load(), via classes RulesEntityController and EntityCRUDController.
 *
 * @param $configs
 *   An array of rules configurations being loaded, keyed by id.
 */
function hook_rules_config_load($configs) {
  $result = db_query('SELECT id, foo FROM {mytable} WHERE id IN(:ids)', array(':ids' => array_keys($configs)));
  foreach ($result as $record) {
    $configs[$record->id]->foo = $record->foo;
  }
}

/**
 * Respond to creation of a new rules configuration.
 *
 * This hook is invoked after the rules configuration is inserted into the
 * the database.
 *
 * @param RulesPlugin $config
 *   The rules configuration that is being created.
 */
function hook_rules_config_insert($config) {
  db_insert('mytable')
    ->fields(array(
      'nid' => $config->id,
      'plugin' => $config->plugin,
    ))
    ->execute();
}

/**
 * Act on a rules configuration being inserted or updated.
 *
 * This hook is invoked before the rules configuration is saved to the
 * database.
 *
 * @param RulesPlugin $config
 *   The rules configuration that is being inserted or updated.
 */
function hook_rules_config_presave($config) {
  if ($config->id && $config->module == 'yours') {
    // Add custom condition.
    $config->conditon(/* Your condition */);
  }
}

/**
 * Respond to updates to a rules configuration.
 *
 * This hook is invoked after the configuration has been updated in the
 * database.
 *
 * @param RulesPlugin $config
 *   The rules configuration that is being updated.
 */
function hook_rules_config_update($config) {
  db_update('mytable')
    ->fields(array('plugin' => $config->plugin))
    ->condition('id', $config->id)
    ->execute();
}

/**
 * Respond to rules configuration deletion.
 *
 * This hook is invoked after the configuration has been removed from the
 * database.
 *
 * @param RulesPlugin $config
 *   The rules configuration that is being deleted.
 */
function hook_rules_config_delete($config) {
  db_delete('mytable')
    ->condition('id', $config->id)
    ->execute();
}

/**
 * Define default rules configurations.
 *
 * This hook is invoked when rules configurations are loaded. The implementation
 * should be placed into the file MODULENAME.rules_defaults.inc, which gets
 * automatically included when the hook is invoked.
 */
function hook_default_rules_configuration() {
  //TODO: example
}

/**
 * Alter default rules configurations.
 *
 * The implementation should be placed into the file
 * MODULENAME.rules_defaults.inc, which gets automatically included when the
 * hook is invoked.
 */
function hook_default_rules_configuration_alter(&$configs) {
  // Add custom condition.
  $configs['foo']->condition('bar');
}

/**
 * @}
 */
