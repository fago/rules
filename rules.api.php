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
 *     the parameter's name as key.  Optional. Each parameter has to be
 *     described by a sub-array with possible attributes as described
 *     afterwards, whereas the name of a parameter needs to be a lowercase,
 *     valid PHP variable name.
 *   - provides: An array describing the variables the action provides to the
 *     evaluation state with the variable name as key. Optional. Each variable
 *     has to be described by a sub-array with possible attributes as described
 *     afterwards, whereas the name of a parameter needs to be a lowercase,
 *     valid PHP variable name.
 *   - 'named parameter': If set to TRUE, the arguments will be passed as a
 *     single array with the parameter names as keys. This emulates named
 *     parameters in PHP and is in particular useful if the number of parameters
 *     can vary. Optionally, defaults to FALSE.
 *   - base: The base for action implementation callbacks to use instead of the
 *     action's name. Optional (defaults to the name).
 *   - callbacks: An array which allows to set specific function callbacks for
 *     the action. The default for each callback is the actions base appended
 *     by '_' and the callback name.
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
 *   - sanitize: Optionally. Allows parameters of type 'text' to demand an
 *     already sanitized argument. If enabled, any user specified value won't be
 *     sanitized itself, but replacements applied by input evaluators are.
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
 * @see hook_rules_action_info()
 */
function hook_rules_condition_info() {
  return array(
    'rules_condition_text_compare' => array(
      'label' => t('Textual comparison'),
      'parameter' => array(
        'text1' => array('label' => t('Text 1'), 'type' => 'text'),
        'text2' => array('label' => t('Text 2'), 'type' => 'text'),
      ),
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
 * For a list of data types defined by rules see rules_rules_core_data_info().
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
 *   - parent: Optionally a parent type may be set to specify a sub-type
 *     relationship, which will be only used for checking compatible types. E.g.
 *     the 'entity' data type is parent of the 'node' data type, thus a node may
 *     be also used for any action needing an 'entity' parameter. Can be set to
 *     any known rules data type.
 *   - 'ui class': Specify a class that is used to generate the configuration UI
 *     to configure parameters of this type. The given class must extend
 *     RulesDataUI and may implement the RulesDataDirectInputFormInterface in
 *     order to allow the direct data input configuration mode. Optionally,
 *     defaults to RulesDataUI.
 *   - 'property info': May be used for non-entity data structures to provide
 *     info about the data properties, such that data selectors via an entity
 *     metadata wrapper are supported. Specify an array as expected by
 *     entity_metadata_wrapper(). Optionally.
 *   - 'creation callback': If 'property info' is given, an optional callback
 *     that makes use of the property info to create a new instance of this
 *     data type. Entities should use hook_entity_info() to specify
 *     'creation callback' instead, as introduced by the entity module. See
 *     rules_action_data_create_array() for an example.
 *   - 'property defaults': May be used for non-entity data structures to
 *     to provide property info defaults for the data properties. Specify an
 *     array as expected by entity_metadata_wrapper(). Optionally.
 *   - group: A group for this element, used for grouping the data types in the
 *     interface. Should start with a capital letter and be translated.
 *     Optional.
 *   - 'token type': The type name as used by the token module. Defaults to the
 *     type name as used by rules. Use FALSE to let token ignore this type.
 *     Optional.
 *   - 'cleaning callback': An optional callback that input evaluators may use
 *     to clean inserted replacements; e.g. this is used by the token evaluator.
 *
 *  @see entity_metadata_wrapper()
 *  @see hook_rules_data_info_alter()
 *  @see rules_rules_core_data_info()
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
 *   - label: A label for the plugin. Start capitalized. Required only for
 *     components (see below).
 *   - class: The implementation class. Has to extend the RulesPlugin class.
 *   - embeddable: A container class in which elements of those plugin may be
 *     embedded or FALSE to disallow embedding. Common classes that are used
 *     here are RulesConditionContainer and RulesActionContainer.
 *   - component: If set to TRUE, the rules admin UI will list elements of those
 *     plugin in the components UI and allows the creation of new components
 *     based upon this plugin. Optional.
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
      'label' => t('Condition set (OR)'),
      'class' => 'RulesOr',
      'embeddable' => 'RulesConditionContainer',
      'component' => TRUE,
      'extenders' => array(
        'RulesPluginUIInterface' => array(
          'class' => 'RulesConditionContainerUI',
        ),
      ),
    ),
    'rule' => array(
      'class' => 'Rule',
      'embeddable' => 'RulesRuleSet',
      'extenders' => array(
        'RulesPluginUIInterface' => array(
          'class' => 'RulesRuleUI',
        ),
      ),
    ),
  );
}

/**
 * Declare provided rules input evaluators.
 *
 * The hook implementation should be placed into the file MODULENAME.rules.inc,
 * which gets automatically included when the hook is invoked.
 * For implementing an input evaluator a class has to be provided which
 * extends the abstract RulesDataInputEvaluator class. Therefore the abstract
 * methods prepare() and evaluate() have to be implemented, as well as access()
 * and help() could be overridden in order to control access permissions or to
 * provide some usage help.
 *
 * @return
 *   An array of information about the module's provided input evaluators. The
 *   array contains a sub-array for each evaluator, with the evaluator name as
 *   the key. Possible attributes for each sub-array are:
 *   - class: The implementation class, which has to extend the
 *     RulesDataInputEvaluator class. Required.
 *   - weight: A weight for controlling the evaluation order of multiple
 *     evaluators. Required.
 *   - type: Optionally, the data types for which the input evaluator should be
 *     used. Defaults to 'text'. Multiple data types may be specified using an
 *     array.
 *
 *  @see class RulesDataInputEvaluator
 *  @see hook_rules_evaluator_info_alter()
 */
function hook_rules_evaluator_info() {
  return array(
    'token' => array(
      'class' => 'RulesTokenEvaluator',
      'type' => array('text', 'uri'),
      'weight' => 0,
     ),
  );
}

/**
 * Declare provided rules data processors.
 *
 * The hook implementation should be placed into the file MODULENAME.rules.inc,
 * which gets automatically included when the hook is invoked.
 * For implementing a data processors a class has to be provided which
 * extends the abstract RulesDataProcessor class. Therefore the abstract
 * method process() has to be implemented, but also the methods form() and
 * access() could be overridden in order to provide a configuration form or
 * to control access permissions.
 *
 * @return
 *   An array of information about the module's provided data processors. The
 *   array contains a sub-array for each processor, with the processor name as
 *   the key. Possible attributes for each sub-array are:
 *   - class: The implementation class, which has to extend the
 *     RulesDataProcessor class. Required.
 *   - weight: A weight for controlling the processing order of multiple data
 *     processors. Required.
 *   - type: Optionally, the data types for which the data processor should be
 *     used. Defaults to 'text'. Multiple data types may be specified using an
 *     array.
 *
 *  @see class RulesDataProcessor
 *  @see hook_rules_data_processor_info_alter()
 */
function hook_rules_data_processor_info() {
  return array(
    'date_offset' => array(
      'class' => 'RulesDateOffsetProcessor',
      'type' => 'date',
      'weight' => -2,
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
 * @see hook_rules_condition_info()
 */
function hook_rules_condition_info_alter(&$conditions) {
  // Change conditions.
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
  // Change events.
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
 * @see hook_rules_data_info()
 */
function hook_rules_data_info_alter(&$data_info) {
  // Change data types.
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
 * @see hook_rules_plugin_info()
 */
function hook_rules_plugin_info_alter(&$plugin_info) {
  // Change plugin info.
}

/**
 * Alter rules input evaluator info.
 *
 * The implementation should be placed into the file MODULENAME.rules.inc, which
 * gets automatically included when the hook is invoked.
 *
 * @param $evaluator_info
 *   The items of all modules as returned from hook_rules_evaluator_info().
 *
 * @see hook_rules_evaluator_info()
 */
function hook_rules_evaluator_info_alter(&$evaluator_info) {
  // Change evaluator info.
}

/**
 * Alter rules data_processor info.
 *
 * The implementation should be placed into the file MODULENAME.rules.inc, which
 * gets automatically included when the hook is invoked.
 *
 * @param $processor_info
 *   The items of all modules as returned from hook_rules_data_processor_info().
 *
 * @see hook_rules_data_processor_info()
 */
function hook_rules_data_processor_info_alter(&$processor_info) {
  // Change processor info.
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
 * Respond to rules configuration execution.
 *
 * This hook is invoked right before the rules configuration is executed.
 *
 * @param RulesPlugin $config
 *   The rules configuration that is being executed.
 */
function hook_rules_config_execute($config) {

}

/**
 * Define default rules configurations.
 *
 * This hook is invoked when rules configurations are loaded. The implementation
 * should be placed into the file MODULENAME.rules_defaults.inc, which gets
 * automatically included when the hook is invoked.
 *
 * @return
 *   An array of rules configurations with the configuration names as keys.
 *
 * @see hook_default_rules_configuration_alter()
 */
function hook_default_rules_configuration() {
  $rule = rules_reaction_rule();
  $rule->label = 'example default rule';
  $rule->active = FALSE;
  $rule->event('node_update')
       ->condition(rules_condition('data_is', array('data:select' => 'node:status', 'value' => TRUE))->negate())
       ->condition('data_is', array('data:select' => 'node:type', 'value' => 'page'))
       ->action('drupal_message', array('message' => 'A node has been updated.'));

  $configs['rules_test_default_1'] = $rule;
  return $config;
}

/**
 * Alter default rules configurations.
 *
 * The implementation should be placed into the file
 * MODULENAME.rules_defaults.inc, which gets automatically included when the
 * hook is invoked.
 *
 * @param $configs
 *   The default configurations of all modules as returned from
 *   hook_default_rules_configuration().
 *
 * @see hook_default_rules_configuration()
 */
function hook_default_rules_configuration_alter(&$configs) {
  // Add custom condition.
  $configs['foo']->condition('bar');
}

/**
 * Alter rules components before execution.
 *
 * This hooks allows altering rules components before they are cached for later
 * re-use. Use this hook only for altering the component in order to prepare
 * re-use through rules_invoke_component() or the provided condition/action.
 * Note that this hook is only invoked for any components cached for execution,
 * but not for components that are programmatically created and executed on the
 * fly (without saving them).
 *
 * @param $plugin
 *   The name of the component plugin.
 * @param $component
 *   The component that is to be cached.
 *
 * @see rules_invoke_component()
 */
function hook_rules_component_alter($plugin, RulesPlugin $component) {

}

/**
 * Alters event sets.
 *
 * This hooks allows altering rules event sets, which contain all rules that are
 * triggered upon a specific event. Rules internally caches all rules associated
 * to an event in an event set, which is cached for fast evaluation. This hook
 * is invoked just before any event set is cached, thus it allows altering of
 * the to be executed rules without the changes to appear in the UI, e.g. to add
 * a further condition to some rules.
 *
 * @param $event_name
 *   The name of the event.
 * @param $event_set
 *   The event set that is to be cached.
 *
 * @see rules_invoke_event()
 */
function hook_rules_event_set_alter($event_name, RulesEventSet $event_set) {

}

/**
 * @}
 */
