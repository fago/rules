<?php
// $Id$

/**
 * @file Contains core ui functions.
 */

/**
 * Plugin UI Interface.
 */
interface RulesPluginUIInterface {

  /**
   * Adds the whole configuration form of this rules configuration. For rule
   * elements that are part of a configuration this method just adds the
   * elements configuration form.
   *
   * @param $form
   *   The form array where to add the form.
   * @param $form_state
   *   The current form state.
   * @param $options
   *   An optional array of options with the known keys:
   *    - 'show settings': Whether to include the 'settings' fieldset for
   *      editing configuration settings like the label or categories. Defaults
   *      to FALSE.
   *    - 'button': Whether a submit button should be added. Defaults to FALSE.
   *    - 'init': Whether the element is about to be configured the first time
   *      and the configuration is about to be initialized. Defaults to FALSE.
   *    - 'restrict plugins: May be used to restrict the list of rules plugins
   *      that may be added to this configuration. For that set an array of
   *      valid plugins. Note that conditions and actions are always valid, so
   *      just set an empty array for just allowing those.
   *    - 'restrict conditions': Optionally set an array of condition names to
   *      restrict the conditions that are available for adding.
   *    - 'restrict actions': Optionally set an array of action names to
   *      restrict the actions that are available to for adding.
   *    - 'restrict events': Optionally set an array of event names to restrict
   *      the events that are available for adding.
   */
  public function form(&$form, &$form_state, $options = array());

  /**
   * Validate the configuration form of this rule element.
   *
   * @param $form
   * @param $form_state
   */
  public function form_validate($form, &$form_state);

  /**
   * Submit the configuration form of this rule element. This makes sure to
   * put the updated configuration in the form state. For saving changes
   * permanently, just call $config->save() afterwards.
   *
   * @param $form
   * @param $form_state
   */
  public function form_submit($form, &$form_state);


  /**
   * Returns a structured array for rendering this element in overviews.
   */
  public function buildContent();

  /**
   * Returns the help text for editing this plugin.
   */
  public function help();

  /**
   * Returns ui operations for this element.
   */
  public function operations();

}

/**
 * Faces UI extender for all kind of Rules plugins. Provides various useful
 * methods for any rules UI.
 */
class RulesPluginUI extends FacesExtender implements RulesPluginUIInterface {

  /**
   * @var RulesPlugin
   */
  protected $element;

  /**
   * Provide $this->element to make the code more meaningful.
   */
  public function __construct(FacesExtendable $object) {
    parent::__construct($object);
    $this->element = $object;
  }

  /**
   * Implements RulesPluginUIInterface.
   * Generates the element edit form.
   */
  public function form(&$form, &$form_state, $options = array()) {
    self::formDefaults($form);

    // Add the help to the top of the form.
    $form['help'] = $this->element->help();

    // We use $form_state['element_settings'] to store the settings of both
    // parameter modes. That way one can switch between the parameter modes
    // without loosing the settings of those.
    $form_state += array('element_settings' => $this->element->settings);
    $settings = $this->element->settings + $form_state['element_settings'];

    $form['parameter'] = array(
      '#tree' => TRUE,
    );

    foreach($this->element->parameterInfo() as $name => $parameter) {
      if ($parameter['type'] == 'hidden') {
        continue;
      }

      $form['parameter'][$name] = array(
        '#type' => 'fieldset',
        '#title' => check_plain($parameter['label']),
        '#description' => check_plain(isset($parameter['description']) ? $parameter['description'] : ''),
      );

      // Init the parameter input mode.
      $form_state['parameter_mode'][$name] = !isset($form_state['parameter_mode'][$name]) ? NULL : $form_state['parameter_mode'][$name];
      $form['parameter'][$name] += $this->getParameterForm($name, $parameter, $settings, $form_state['parameter_mode'][$name]);
    }

    // Add settings form, if specified.
    if (!empty($options['show settings'])) {
      $this->settingsForm($form, $form_state);
    }
    // Add submit button, if specified.
    if (!empty($options['button'])) {
      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'),
        '#weight' => 10,
      );
    }
  }

  /**
   * Actually generates the parameter form for the given data type.
   */
  protected function getParameterForm($name, $info, $settings, &$mode) {
    $class = $this->getDataTypeClass($info['type']);

    // Init the mode.
    if (!isset($mode)) {
      if (isset($settings[$name . ':select'])) {
        $mode = 'selector';
      }
      elseif (isset($settings[$name]) && $class instanceof RulesDataDirectInputForm) {
        $mode = 'input';
      }
      elseif (isset($info['restriction'])) {
        $mode = $info['restriction'];
      }
      else {
        $mode = call_user_func(array($class, 'getDefaultMode'));
      }
    }
    $supports_input_mode = in_array('RulesDataDirectInputForm', class_implements($class));

    // Add the parameter form.
    if ($mode == 'input' && $supports_input_mode) {
      $form['settings'] = call_user_func(array($class, 'inputForm'), $name, $info, $settings, $this->element);
    }
    else {
      $form['settings'] = call_user_func(array($class, 'selectionForm'), $name, $info, $settings, $this->element);
    }

    // Add a link for switching the input mode when JS is enabled and a button
    // to switch it without javascript, in case switching is possible.
    if ($supports_input_mode && empty($info['restriction'])) {
      $value = $mode == 'selector' ? t('Switch to the direct input mode') : t('Switch to data selection');
      $link_id = drupal_html_id('rules-parameter-' . $name . '-switch-link');

      $form['link'] = array(
        '#type' => 'link',
        '#href' => '#',
        '#title' => $value,
        // #id is used for ajax js code generation.
        '#id' => $link_id,
        // Needed to actually output the #id.
        '#options' => array('attributes' => array('id' => $link_id)),
        '#process' => array('ajax_process_form'),
        // Hide the link by default and unhide it by javascript.
        '#prefix' => '<div class="rules-show-js">',
        '#suffix' => '</div>',
        '#ajax' => rules_ui_form_default_ajax('none') + array(
          'event' => 'click',
          // Trigger as the Switch button.
          'trigger_as' => array(
            'name' => 'param_' . $name,
            'value' => t('Switch'),
          ),
        ),
      );
      $form['switch_button'] = array(
        '#type' => 'submit',
        '#name' => 'param_' . $name,
        '#attributes' => array('class' => array('rules-hide-js')),
        '#parameter' => $name,
        '#value' => t('Switch'),
        '#submit' => array('rules_ui_parameter_replace_submit'),
        '#ajax' => rules_ui_form_default_ajax('none'),
        // Do not validate!
        '#limit_validation_errors' => array(),
      );
    }
    return $form;
  }

  /**
   * Implements RulesPluginUIInterface.
   */
  public function form_validate($form, &$form_state) {
    $this->form_extract_values($form, $form_state);

    if (!$this->element->access()) {
      form_set_error(NULL, t('Access violation!'));
    }
    try {
      $this->element->integrityCheck();
    }
    catch (RulesException $e) {
      form_set_error(implode('][',$e->keys), $e->getMessage());
    }
    if (!empty($form['settings'])) {
      $this->settingsFormValidate($form, $form_state);
    }
  }

  /**
   * Applies the values of the form to the element.
   */
  public function form_extract_values($form, &$form_state) {
    $this->element->settings = array();
    foreach ($form_state['values']['parameter'] as $name => $values) {
      $this->element->settings += $values['settings'];
    }
    $this->element->processSettings(TRUE, TRUE);

    if (!empty($form['settings'])) {
      $this->settingsFormExtractValues($form, $form_state);
    }
  }

  /**
   * Implements RulesPluginUIInterface.
   */
  public function form_submit($form, &$form_state) {
    if (!empty($form['settings'])) {
      $this->settingsFormSubmit($form, $form_state);
    }
    $this->element->save();
    // Clear the element id index map, so the map is regenerated in case of a
    // form rebuild. That way the map is indentical to the map that would be
    // generated on the next page load.
    self::clearElementMap($this->element);
    drupal_set_message(t('Your changes have been saved.'));
  }

  /**
   * Adds the configuration settings form (label, tags, description, ..).
   */
  public function settingsForm(&$form, &$form_state) {
    // Add the settings in a separate fieldset below.
    $form['settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#weight' => 5,
    );
    $form['settings']['label'] = array(
      '#type' => 'textfield',
      '#title' => 'Label',
      '#default_value' => $this->element->label(),
      '#required' => TRUE,
      '#weight' => -5,
      '#description' => t('The human-readable name.'),
    );
    if (!empty($this->element->module) && !empty($this->element->name) && strpos($this->element->name, $this->element->module . '_') === 0) {
      // Remove the module prefix from the machine name.
      $machine_name = substr($this->element->name, strlen($this->element->module) + 1);
    }
    $form['settings']['name'] = array(
      '#title' => t('Machine name'),
      '#type' => 'textfield',
      '#default_value' => isset($machine_name) ? $machine_name : $this->element->name,
      '#maxlength' => 32,
      '#required' => TRUE,
      '#description' => t('The machine-readable name of this configuration is used by rules internally to identify the configuration. This name must contain only lowercase letters, numbers, and underscores and must be unique.'),
    );
    // Add the JS for machine name creation only during creation, so label
    // changes don't automatically lead to changed machine names.
    if (empty($this->element->id)) {
      $js_settings = array(
        'type' => 'setting',
        'data' => array(
          'machineReadableValue' => array(
            'label' => array(
              'text' => t('Machine name'),
              'target' => 'name',
              'searchPattern' => '[^a-z0-9]+',
              'replaceToken' => '_',
            ),
          ),
        ),
      );
      $form['settings']['name']['#attached'] = array(
        'js' => array(drupal_get_path('module', 'system') . '/system.js', $js_settings),
      );
      $form['settings']['label']['#field_suffix'] = '&nbsp;<small id="edit-label-suffix">' . t('Machine name: @name', array('@name' => $form['settings']['name']['#default_value'])) . '</small>';
    }
    // TODO: Attach field form thus description and tags.
  }

  public function settingsFormExtractValues($form, &$form_state) {
    $this->element->label = $form_state['values']['label'];

    // If the name was changed we have to redirect to the URL that contains
    // the new name, instead of rebuilding on the old URL with the old name
    if ($form['settings']['name']['#default_value'] != $form_state['values']['name']) {
      $module = isset($this->element->module) ? $this->element->module : 'rules';
      $this->element->name = $module . '_' . $form_state['values']['name'];
      $form_state['redirect'] = RulesPluginUI::path($this->element->name);
    }
  }

  public function settingsFormValidate($form, &$form_state) {
    if (!preg_match('!^[a-z0-9_]+$!', $form_state['values']['name'])) {
      form_set_error('name', t('The machine-readable name must contain only lowercase letters, numbers, and underscores.'));
    }
    elseif ($form['settings']['name']['#default_value'] != $form_state['values']['name'] && rules_config_load($this->element->name)) {
      form_set_error('name', t('The machine-readable name %name is already taken.', array('%name' => $form_state['values']['name'])));
    }
  }

  public function settingsFormSubmit($form, &$form_state) {

  }

  /**
   * Returns the name of class for the given data type.
   *
   * @param $data_type
   *  The name of the data typ
   */
  public function getDataTypeClass($data_type) {
    $cache = rules_get_cache();
    $data_info = $cache['data_info'];
    return (is_string($data_type) && isset($data_info[$data_type]['ui class'])) ? $data_info[$data_type]['ui class'] : 'RulesDataUI';
  }

  /**
   * Implements RulesPluginUIInterface.
   * Show a preview of the configuration settings.
   */
  public function buildContent() {
    $content['label'] = array(
      '#type' => 'link',
      '#title' => $this->element->label(),
      '#href' => RulesPluginUI::path($this->element->root()->name, 'edit', $this->childElementId()),
      '#prefix' => '<div class="rules-element-label">',
      // Put the elements below in a "description" div.
      '#suffix' => '</div><div class="description">'
    );
    $content['parameter'] = array(
      '#heading' => t('Parameter'),
      '#theme' => 'rules_content_group',
    );
    foreach ($this->element->parameterInfo() as $name => $parameter) {
      $element = array();
      if (isset($this->element->settings[$name . ':select'])) {
        $element['content'] = array(
         '#markup' => '[' . $this->element->settings[$name . ':select'] . ']',
        );
      }
      elseif (isset($this->element->settings[$name]) && (!isset($parameter['default value']) || $parameter['default value'] != $this->element->settings[$name])) {
        $element = call_user_func(array($this->getDataTypeClass($parameter['type']), 'render'), $this->element->settings[$name]);
      }
      // Only add parameters that are really configured / not default.
      if ($element) {
        $content['parameter'][$name] = array(
          '#theme' => 'rules_parameter_configuration',
          '#info' => $parameter,
        ) + $element;
      }
    }
    $content['#suffix'] = '</div>';
    $content['#type'] = 'container';
    $content['#attributes']['class'][] = 'rules-element-content';
    return $content;
  }

  /**
   * Implements RulesPluginUIInterface.
   */
  public function operations() {
    $name = $this->element->root()->name;
    $element_id = $this->childElementId();
    $render = array(
      '#theme' => 'links__rules',
    );
    $render['#attributes']['class'][] = 'rules-operations';
    $render['#attributes']['class'][] = 'action-links';
    $render['#links']['edit'] = array(
      'title' => t('edit'),
      'href' => RulesPluginUI::path($name, 'edit', $element_id),
      'query' => drupal_get_destination(),
    );
    $render['#links']['delete'] = array(
      'title' => t('delete'),
      'href' => RulesPluginUI::path($name, 'delete', $element_id),
      'query' => drupal_get_destination(),
    );
    return $render;
  }

  /**
   * Implements RulesPluginUIInterface.
   */
  public function help() {}

  /**
   * Returns the child element id of the given child.
   */
  public function childElementId($element = NULL) {
    $element = isset($element) ? $element : $this->element;
    self::indexConfig($element);
    return $element->childElementId;
  }

  /**
   * Returns the child element associated with the given id.
   */
  public static function getChildElementById($rules_config, $id) {
    $rules_config = $rules_config->root();
    self::indexConfig($rules_config);
    if (isset($rules_config->map[$id])) {
      return $rules_config->map[$id];
    }
    return FALSE;
  }

  /**
   * Generates child element ids for lookup up elements.
   */
  protected static function indexConfig(RulesPlugin $element, &$map = array()) {
    // Generate them only once.
    if (!isset($element->childElementId)) {
      $counter = &drupal_static(__FUNCTION__);

      if (!$map) {
        // In case a new element has no id yet, get the previous map.
        if (!empty($element->root()->map)) {
          $map =& $element->root()->map;
        }
        elseif ($element->root() === $element) {
          $counter = 0;
          $element->map = array();
          $map =& $element->map;
        }
        else {
          self::indexConfig($element->root(), $map);
          return;
        }
      }
      $counter++;
      $map[$counter] = $element;
      $element->childElementId = $counter;

      if ($element instanceof RulesContainerPlugin) {
        foreach ($element as $child) {
          self::indexConfig($child, $map);
        }
      }
    }
  }

  /**
   * Clears the current element map.
   */
  protected static function clearElementMap($element) {
    $element = $element->root();
    if (!empty($element->map)) {
      foreach ($element->map as $child) {
        unset($child->childElementId);
      }
      unset($element->map);
    }
  }

  /**
   * Generates the render array for a overview configuration table for arbitrary
   * rule configs that match the given conditions.
   *
   * @param $conditions
   *   An array of conditions as needed by rules_config_load_multiple().
   * @param $empty_text
   *   The text to display if there are no configs matching the conditions.
   * @return Array
   *   A renderable array.
   */
  public static function overviewTable($conditions = array(), $empty_text) {
    $configs = rules_config_load_multiple(FALSE, $conditions);
    $rows = array();
    $event_info = rules_fetch_data('event_info');

    foreach ($configs as $name => $config) {
      $row = array();
      $row[] = l($config->label(), RulesPluginUI::path($name));
      // Add events if the configs are assigned to events.
      if ($config instanceof RulesTriggerableInterface) {
        $events = array();
        foreach ($config->events() as $event_name) {
          $events[] = $event_info[$event_name]['label'];
        }
        $row[] = implode(", ", $events);
      }
      // Add exportable status.
      $row[] = array('data' => array(
        '#theme' => 'entity_status',
        '#status' => $config->status,
      ));

      $row[] = l(t('edit'), RulesPluginUI::path($name));
      // Add either an enable or disable link.
      $text = $config->active ? t('disable') : t('enable');
      $link_path = RulesPluginUI::path($name, $config->active ? 'disable' : 'enable');
      $row[] = l($text, $link_path, array('query' => drupal_get_destination()));
      $row[] = l(t('clone'), RulesPluginUI::path($name, 'clone'), array('query' => drupal_get_destination()));

      // TODO: hide configs where access() is FALSE and make sure access is
      // FALSE for fixed configurations,

      // Add either an delete or revert link.
      if ($config->status != ENTITY_IN_CODE) {
        $text = $config->status & ENTITY_IN_CODE ? t('revert') : t('delete');
        $link_path = RulesPluginUI::path($name, $config->status & ENTITY_IN_CODE ? 'revert' : 'delete');
        $row[] = l($text, $link_path, array('query' => drupal_get_destination()));
      }
      else {
        $row[] = '';
      }

      $rows[] = $row;
    }
    $header = array(t('Label'), t('Event'), t('Status'), array('data' => t('Operations'), 'colspan' => 4));
    if (!isset($events)) {
      // Remove the event heading as there is no such column.
      unset($header[1]);
    }

    $table = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $empty_text,
    );
    return $table;
  }

  /**
   * Generates a path using the given operation for the element with the given
   * id of the configuration with the given name.
   */
  public static function path($name, $op = NULL, $element_id = NULL) {
    return implode('/', array_filter(array(RULES_UI_RULE_PATH, $name, $op, $element_id)));
  }

  /**
   * Returns an array of options to use with a select for the items specified
   * in the given hook.
   */
  public static function getOptions($hook) {
    $sorted_data = array();
    $data = rules_fetch_data($hook);
    foreach ($data as $name => $set) {
      $sorted_data[ucfirst($set['group'])][$name] = $set['label'];
    }
    foreach ($sorted_data as $key => $set) {
      asort($set);
      $sorted_data[$key] = $set;
    }
    return $sorted_data;
  }

  public static function formDefaults(&$form) {
    // Make sure collapsible fieldsets work even if added via AJAX.
    // See http://drupal.org/node/768128.
    $form['#attached']['js'][] = 'misc/collapse.js';

    // Add our own css.
    $form['#attached']['css'][] = drupal_get_path('module', 'rules') . '/ui/rules.ui.css';

    // Specify the wrapper div used by #ajax.
    $form['#prefix'] = '<div id="rules-form-wrapper">';
    $form['#suffix'] = '</div>';
  }
}

/**
 * UI for abstract plugins (conditions & actions).
 */
class RulesAbstractPluginUI extends RulesPluginUI {

  /**
   * Overridden to invoke the abstract plugins form alter callback.
   */
  public function form(&$form, &$form_state, $options = array()) {
    parent::form($form, $form_state, $options);
    $this->element->call('form_alter', array(&$form, &$form_state));
  }
}

/**
 * UI for Rules Container.
 */
class RulesContainerPluginUI extends RulesPluginUI {

  /**
   * Generates a table for editing the contained elements.
   */
  public function form(&$form, &$form_state, $options = array(), $iterator = NULL) {
    self::formDefaults($form);

    $form['elements'] = array(
      // Hide during creation.
      '#access' => empty($options['init']),
      '#tree' => TRUE,
      '#theme' => 'rules_elements',
      '#empty' => t('None'),
      '#heading' => t('Elements')
    );
    $form['elements']['#attributes']['class'][] = 'rules-container-plugin';

    // Recurse over all element childrens or use the provided iterator.
    $iterator = isset($iterator) ? $iterator : new RecursiveIteratorIterator($this->element, RecursiveIteratorIterator::SELF_FIRST);
    $root_depth = $this->element->depth();
    foreach ($iterator as $key => $child) {
      $id = self::childElementId($child);

      $is_container = $child instanceof RulesContainerPlugin;
      $form['elements'][$id] = array(
        '#depth' => $child->depth() - $root_depth - 1,
        '#container' => $is_container,
      );
      $form['elements'][$id]['label'] = $child->buildContent();
      $form['elements'][$id]['weight'] = array(
        '#type' => 'weight',
        '#default_value' => $child->weight,
      );
      $form['elements'][$id]['parent_id'] = array(
        '#type' => 'hidden',
        '#default_value' => self::childElementId($child->property('parent')),
      );
      $form['elements'][$id]['element_id'] = array(
        '#type' => 'hidden',
        '#default_value' => $id,
      );
      $form['elements'][$id]['operations'] = $child->operations();
    }

    // Add settings form, if specified.
    if (!empty($options['show settings'])) {
      $this->settingsForm($form, $form_state);
    }
    // Add submit button, if specified.
    if (!empty($options['button'])) {
      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => empty($options['init']) ? t('Save changes') : t('Continue'),
        '#weight' => 10,
      );
    }
  }

  /**
   * Applies the values of the form to the given rule configuration.
   */
  public function form_extract_values($form, &$form_state) {
    // Get the right form values.
    $values = $form_state['values'];
    foreach ($form['#parents'] as $key) {
      $values = isset($values[$key]) ? $values[$key] : NULL;
    }
    // Now apply the new hierarchy.
    if (isset($values['elements'])) {
      $root_element = $this->element->root();
      foreach ($values['elements'] as $id => $data) {
        $child = self::getChildElementById($root_element, $id);
        $child->weight = $data['weight'];
        $parent = self::getChildElementById($root_element, $data['parent_id']);
        $child->setParent($parent ? $parent : $this->element);
      }
      $this->element->sortChildren(TRUE);
    }
    if (!empty($form['settings'])) {
      $this->settingsFormExtractValues($form, $form_state);
    }
  }

  public function operations() {
    $ops = parent::operations();
    $add_ops = self::addOperations();
    $ops['#links'] += $add_ops['#links'];
    return $ops;
  }

  /**
   * Gets the Add-* operations for the given element.
   */
  public function addOperations() {
    $name = $this->element->root()->name;
    $element_id = $this->childElementId();
    $render = array(
      '#theme' => 'links__rules',
    );
    $render['#attributes']['class'][] = 'rules-operations-add';
    $render['#attributes']['class'][] = 'action-links';
    foreach (rules_fetch_data('plugin_info') as $plugin => $info) {
      if (!empty($info['embeddable']) && $this->element instanceof $info['embeddable']) {
        $render['#links']['add_' . $plugin] = array(
          'title' => t('Add !name', array('!name' => $plugin)),
          'href' => RulesPluginUI::path($name, 'add/' . $plugin, $element_id),
          'query' => drupal_get_destination(),
        );
      }
    }
    return $render;
  }


  public function buildContent() {
    $content = parent::buildContent();
    // Don't link the title for container plugins.
    $content['label'] = array('#markup' => check_plain($content['label']['#title']));
    return $content;
  }
}

/**
 * UI for Rules condition container.
 */
class RulesConditionContainerUI extends RulesContainerPluginUI {

  public function form(&$form, &$form_state, $options = array(), $iterator = NULL) {
    parent::form($form, $form_state, $options, $iterator);
    // Add the add-* operation links.
    $form['add'] = self::addOperations();
    // Hide that during creation.
    $form['add']['#access'] = empty($options['init']);
    $form['elements']['#attributes']['class'][] = 'rules-condition-container';
    $form['elements']['#heading'] = t('Conditions');

    // By default skip
    if (!empty($options['init'])) {
      $config = $this->element->root();
      $form['init_help'] = array(
        '#type' => 'container',
        '#id' => 'rules-plugin-add-help',
        'content' => array(
          '#markup' => t('You are about to add a new @plugin to the @config-plugin %label.', array('@plugin' => $this->element->plugin(), '@config-plugin' => $config->plugin(), '%label' => $config->label())),
        ),
      );
    }
  }
}

/**
 * UI for Rules action container.
 */
class RulesActionContainerUI extends RulesContainerPluginUI {

  public function form(&$form, &$form_state, $options = array(), $iterator = NULL) {
    parent::form($form,  $form_state, $options, $iterator);
    // Add the add-* operation links.
    $form['add'] = self::addOperations();
    // Hide that during creation.
    $form['add']['#access'] = empty($options['init']);
    $form['elements']['#attributes']['class'][] = 'rules-action-container';
    $form['elements']['#heading'] = t('Actions');
  }
}
