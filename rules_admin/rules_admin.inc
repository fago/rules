<?php
// $Id$

/**
 * @file Rules Admin UI
 *   Implements rule management and configuration screens.
 */

/**
 * Reaction rules overview.
 */
function rules_admin_reaction_overview($form, &$form_state, $base_path) {
  form_load_include($form_state, 'inc', 'rules', 'ui/ui.forms');
  // Set the static base path.
  RulesPluginUI::$basePath = $base_path;

  $form['filter'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filter'),
    '#collapsible' => TRUE,
  );
  $form['filter']['#id'] = 'rules-filter-form';
  $form['filter']['#attached']['css'][] = drupal_get_path('module', 'rules') . '/ui/rules.ui.css';
  $form['filter']['event'] = array(
    '#type' => 'select',
    '#title' => t('Filter by event'),
    '#options' => array(0 => '<All>') + RulesPluginUI::getOptions('event_info'),
    '#default_value' => isset($form_state['values']['event']) ? $form_state['values']['event'] : 0,
  );
  $form['filter']['category'] = array(
    '#type' => 'select',
    '#title' => t('Filter by category'),
    '#options' => array(0 => '<All>'),
    '#default_value' => isset($form_state['values']['category']) ? $form_state['values']['category'] : 0,
  );
  $form['filter']['submit'] = array('#type' => 'submit', '#value' => t('Filter'));

  $event = !empty($form_state['values']['event']) ? $form_state['values']['event'] : FALSE;
  $category = !empty($form_state['values']['category']) ? $form_state['values']['category'] : FALSE;

  $conditions = array('event' => $event, 'plugin' => 'reaction rule', 'active' => TRUE);
  $options = array('show plugin' => FALSE);
  $form['active'] = RulesPluginUI::overviewTable(array_filter($conditions), $options);
  $form['active']['#caption'] = t('Active rules');
  $form['acitve']['#empty'] = t('There are no matching active reaction rules.');

  $conditions = array_filter(array('event' => $event, 'plugin' => 'reaction rule')) + array('active' => FALSE);
  $form['inactive'] = RulesPluginUI::overviewTable($conditions, $options);
  $form['inactive']['#caption'] = t('Inactive rules');
  $form['inactive']['#empty'] = t('There are no matching inactive reaction rules.');

  $form['filter']['#collapsed'] = isset($form_state['values']) || (count($form['active']['#rows']) + count($form['inactive']['#rows'])) < 20;
  $form['#submit'][] = 'rules_form_submit_rebuild';
  return $form;
}

/**
 * Components overview.
 */
function rules_admin_components_overview($form, &$form_state, $base_path) {
  // Set the static base path.
  RulesPluginUI::$basePath = $base_path;

  RulesPluginUI::formDefaults($form, $form_state);

  $form['filter'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filter'),
    '#collapsible' => TRUE,
  );
  $form['filter']['#id'] = 'rules-filter-form';
  $form['filter']['#attached']['css'][] = drupal_get_path('module', 'rules') . '/ui/rules.ui.css';
  $form['filter']['plugin'] = array(
    '#type' => 'select',
    '#title' => t('Filter by plugin'),
    '#options' => array(0 => '<All>') + rules_admin_component_options(),
    '#default_value' => isset($form_state['values']['plugin']) ? $form_state['values']['plugin'] : 0,
  );
  $form['filter']['category'] = array(
    '#type' => 'select',
    '#title' => t('Filter by category'),
    '#options' => array(0 => '<All>'),
    '#default_value' => isset($form_state['values']['category']) ? $form_state['values']['category'] : 0,
  );
  $form['filter']['submit'] = array('#type' => 'submit', '#value' => t('Filter'));

  $category = !empty($form_state['values']['category']) ? $form_state['values']['category'] : FALSE;

  // Get the plugin name usable as component.
  $plugins = array_keys(rules_filter_array(rules_fetch_data('plugin_info'), 'component', TRUE));
  $conditions = array('plugin' => !empty($form_state['values']['plugin']) ? array($form_state['values']['plugin']) : $plugins);
  $form['table'] = RulesPluginUI::overviewTable($conditions, array('hide status op' => TRUE));
  $form['table']['#empty'] = t('There are no rule components.');

  $form['filter']['#collapsed'] = isset($form_state['values']) || count($form['table']['#rows']) < 20;
  $form['#submit'][] = 'rules_form_submit_rebuild';
  return $form;
}

/**
 * Rules settings form.
 */
function rules_admin_settings($form, &$form_state) {
  $form['rules_debug'] = array(
    '#type' => 'checkbox',
    '#title' => t('Debug rules evaluation'),
    '#default_value' => variable_get('rules_debug', FALSE),
    '#description' => t('If activated debugging information is shown when rules are evaluated.'),
  );
  return system_settings_form($form);
}

/**
 * Add reaction rules form.
 */
function rules_admin_add_reaction_rule($form, &$form_state, $base_path) {
  RulesPluginUI::formDefaults($form, $form_state);

  $rules_config = rules_reaction_rule();
  $rules_config->form($form, $form_state, array('show settings' => TRUE, 'button' => TRUE));

  $form['settings']['#collapsible'] = FALSE;
  $form['settings']['#type'] = 'container';
  $form['settings']['label']['#default_value'] = '';

  // Hide the rule elements stuff for now.
  foreach (array('elements', 'conditions', 'add', 'events') as $key) {
    $form[$key]['#access'] = FALSE;
  }
  foreach (array('active', 'weight') as $key) {
    $form['settings'][$key]['#access'] = FALSE;
  }
  // Incorporate the form to add the first event.
  $form['settings'] += rules_ui_add_event(array(), $form_state, $rules_config, $base_path);

  unset($form['settings']['submit']);
  $form['submit']['#value'] = t('Save');

  $form_state += array('rules_config' => $rules_config);
  $form['#validate'][] = 'rules_ui_add_event_validate';
  return $form;
}

/**
 * Form add reaction_rule submit callback.
 */
function rules_admin_add_reaction_rule_submit($form, &$form_state) {
  $rules_config = $form_state['rules_config'];
  $rules_config->form_submit($form, $form_state);
  $form_state['redirect'] = RulesPluginUI::path($rules_config->name);
}

/**
 * Add component form.
 */
function rules_admin_add_component($form, &$form_state, $base_path) {
  RulesPluginUI::$basePath = $base_path;
  RulesPluginUI::formDefaults($form, $form_state);

  $form['plugin_name'] = array(
    '#type' => 'select',
    '#title' => t('Component plugin'),
    '#options' => rules_admin_component_options(),
    '#description' => t('Choose which kind of component to create.'),
    '#weight' => -2,
    '#default_value' => isset($form_state['values']['plugin_name']) ? $form_state['values']['plugin_name'] : '',
  );

  if (!isset($form_state['rules_config'])) {
    $form['continue'] = array(
      '#type' => 'submit',
      '#name' => 'continue',
      '#submit' => array('rules_admin_add_component_create_submit'),
      '#value' => t('Continue'),
    );
  }
  else {
    $form['plugin_name']['#disabled'] = TRUE;
    $form_state['rules_config']->form($form, $form_state, array('show settings' => TRUE, 'button' => TRUE, 'init' => TRUE));
    $form['settings']['#collapsible'] = FALSE;
    $form['settings']['#type'] = 'container';
    $form['settings']['label']['#default_value'] = '';
    $form['settings']['#weight'] = -1;

    // Hide the rule elements stuff for now.
    foreach (array('elements', 'negate') as $key) {
      $form[$key]['#access'] = FALSE;
    }
    foreach (array('active', 'weight') as $key) {
      $form['settings'][$key]['#access'] = FALSE;
    }
  }
  return $form;
}

function rules_admin_component_options() {
  $cache = rules_get_cache();
  return rules_extract_property(rules_filter_array($cache['plugin_info'], 'component', TRUE), 'label');
}

/**
 * Submit callback that creates the new component object initially.
 */
function rules_admin_add_component_create_submit($form, &$form_state) {
  $form_state['rules_config'] = rules_plugin_factory($form_state['values']['plugin_name']);
  $form_state['rebuild'] = TRUE;
}

/**
 * Validation callback for adding a component.
 */
function rules_admin_add_component_validate($form, &$form_state) {
  if (isset($form_state['rules_config'])) {
    $form_state['rules_config']->form_validate($form, $form_state);
  }
}

/**
 * Final submit callback for adding a component.
 */
function rules_admin_add_component_submit($form, &$form_state) {
  $rules_config = $form_state['rules_config'];
  $rules_config->form_submit($form, $form_state);
  $form_state['redirect'] = RulesPluginUI::path($rules_config->name);
}

/**
 * Controller class for customizing the default Entity UI.
 */
class RulesUIController extends EntityDefaultUIController {

  public function __construct() {
    $entity_info = entity_get_info('rules_config');
    parent::__construct('rules_config', $entity_info);
  }

  /**
   * Overrides EntityDefaultUIController::hook_menu()
   */
  public function hook_menu() {
    // Reaction rules UI menu entries.
    $reaction_path = $this->path . '/reaction';
    $items = $this->config_menu($reaction_path);
    $items[$this->path] = array(
      'title' => 'Rules',
      'position' => 'right',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_admin_reaction_overview', $reaction_path),
      'description' => 'Manage reaction rules and rule components.',
      'access arguments' => array('administer rules'),
      'file' => 'rules_admin.inc',
      'file path' => drupal_get_path('module', 'rules_admin'),
    );
    $items[$reaction_path] = array(
      'title' => 'Reaction rules',
      'type' => MENU_DEFAULT_LOCAL_TASK,
      'weight' => -1,
    );
    $items[$reaction_path . '/add'] = array(
      'title' => 'Add new rule',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_admin_add_reaction_rule', $reaction_path),
      'access arguments' => array('administer rules'),
      'type' => MENU_LOCAL_ACTION,
      'file' => 'rules_admin.inc',
      'file path' => drupal_get_path('module', 'rules_admin'),
      'weight' => 0,
    );

    // Components UI menu entries.
    $component_path = $this->path . '/components';
    $items += $this->config_menu($component_path);
    $items[$component_path] = array(
      'title' => 'Components',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_admin_components_overview', $component_path),
      'access arguments' => array('administer rules'),
      'type' => MENU_LOCAL_TASK,
      'file' => 'rules_admin.inc',
      'file path' => drupal_get_path('module', 'rules_admin'),
      'weight' => 0,
    );
    $items[$component_path . '/add'] = array(
      'title' => 'Add new component',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_admin_add_component', $component_path),
      'access arguments' => array('administer rules'),
      'type' => MENU_LOCAL_ACTION,
      'file' => 'rules_admin.inc',
      'file path' => drupal_get_path('module', 'rules_admin'),
      'weight' => 0,
    );

    $items[$this->path .'/settings'] = array(
      'title' => 'Settings',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_admin_settings'),
      'access arguments' => array('administer rules'),
      'type' => MENU_LOCAL_TASK,
      'file' => 'rules_admin.inc',
      'file path' => drupal_get_path('module', 'rules_admin'),
      'weight' => 1,
    );
    return $items;
  }

  /**
   * Generates menu items to manipulate rules configurations.
   *
   * @param $base_path
   *   The path to the overview page from where the configurations are edited.
   */
  public function config_menu($base_path) {
    $items = array();
    $base_count = count(explode('/', $base_path));
    $items[$base_path . '/manage/%rules_config'] = array(
      'title callback' => 'rules_get_title',
      'title arguments' => array('Editing !plugin "!label"', $base_count + 1),
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_form_edit_rules_config', $base_count + 1, $base_path),
      'access arguments' => array('administer rules'),
      'description' => 'Manage reaction rules and rule components.',
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/add/%rules_element'] = array(
      'title callback' => 'rules_get_title',
      'title arguments' => array('Add a new !plugin', $base_count + 3),
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_add_element', $base_count + 1, $base_count + 4, $base_count + 3, $base_path),
      'access arguments' => array('administer rules'),
      'load arguments' => array($base_count + 1),
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/add/event'] = array(
      'title callback' => 'rules_get_title',
      'title arguments' => array('Addin events to !plugin "!label"', $base_count + 1),
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_add_event', $base_count + 1, $base_path),
      'access arguments' => array('administer rules'),
      'load arguments' => array($base_count + 1),
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/delete/event'] = array(
      //@todo: improve title.
      'title' => 'Remove event',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_remove_event', $base_count + 1, $base_count + 4, $base_path),
      'access arguments' => array('administer rules'),
      'description' => 'Remove an event from a reaction rule.',
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/edit/%rules_element'] = array(
      'title callback' => 'rules_get_title',
      'title arguments' => array('Editing !plugin "!label"', $base_count + 3),
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_edit_element', $base_count + 1, $base_count + 3, $base_path),
      'access arguments' => array('administer rules'),
      'load arguments' => array($base_count + 1),
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/autocomplete'] = array(
      'page callback' => 'rules_ui_form_data_selection_auto_completion',
      'page arguments' => array($base_count + 3, $base_count + 4, $base_count + 5),
      'access arguments' => array('administer rules'),
      'type' => MENU_CALLBACK,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/delete/%rules_element'] = array(
      'title callback' => 'rules_get_title',
      'title arguments' => array('Editing !plugin "!label"', $base_count + 3),
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_delete_element', $base_count + 1, $base_count + 3, $base_path),
      'access arguments' => array('administer rules'),
      'load arguments' => array($base_count + 1),
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/%'] = array(
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_form_rules_config_confirm_op', $base_count + 1, $base_count + 2, $base_path),
      'access arguments' => array('administer rules'),
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/clone'] = array(
      'title callback' => 'rules_get_title',
      'title arguments' => array('Cloning !plugin "!label"', $base_count + 1),
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_form_clone_rules_config', $base_count + 1, $base_count + 2, $base_path),
      'access arguments' => array('administer rules'),
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    $items[$base_path . '/manage/%rules_config/execute'] = array(
      'title callback' => 'rules_get_title',
      'title arguments' => array('Executing !plugin "!label"', $base_count + 1),
      'page callback' => 'drupal_get_form',
      'page arguments' => array('rules_ui_form_execute_rules_config', $base_count + 1, $base_path),
      'access arguments' => array('administer rules'),
      'type' => MENU_VISIBLE_IN_BREADCRUMB,
      'file' => 'ui/ui.forms.inc',
      'file path' => drupal_get_path('module', 'rules'),
    );
    if (module_exists('rules_scheduler')) {
      $items[$base_path . '/manage/%rules_config/schedule'] = array(
        'title callback' => 'rules_get_title',
        'title arguments' => array('Schedule !plugin "!label"', $base_count + 1),
        'page callback' => 'drupal_get_form',
        'page arguments' => array('rules_scheduler_schedule_form', $base_count + 1, $base_path),
        'access arguments' => array('administer rules'),
        'type' => MENU_VISIBLE_IN_BREADCRUMB,
        'file' => 'rules_scheduler.admin.inc',
        'file path' => drupal_get_path('module', 'rules_scheduler'),
      );
    }
    return $items;
  }
}
