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
   * Returns the whole configuration form of this rules configuration. For rule
   * elements that are part of a configuration this method just returns the
   * elements configuration form.
   *
   * @param $form
   *   The form array.
   * @param $form_state
   *   The current form state.
   * @param $options
   *   An optional array of options with the known keys:
   *    - 'show settings': Whether to include the 'settings' fieldset for
   *      editing configuration settings like the label or categories. Defaults
   *      to FALSE.
   *    - 'button': Whether a submit button should be added. Defaults to FALSE.
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
  public function form_validate(&$form, &$form_state);

  /**
   * Submit the configuration form of this rule element. This makes sure to
   * put the updated configuration in the form state. For saving changes
   * permanently, just call $config->save() afterwards.
   *
   * @param $form
   * @param $form_state
   */
  public function form_submit(&$form, &$form_state);


  /**
   * Returns a structured array for rendering this element.
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
    // Make sure collapsible fieldsets work even if added via AJAX.
    // See http://drupal.org/node/768128.
    $form['#attached']['js'][] = 'misc/collapse.js';

    // Add the help to the top of the form.
    $form['help'] = $this->element->help();

    $data_info = rules_fetch_data('data_info');
    // Make the element available in the form state.
    $form_state['rules_element'] = $this->element;

    // We use $form_state['element_settings'] to store the settings of both
    // parameter modes. That way one can switch between the parameter modes
    // without loosing the settings of those.
    $form_state += array('element_settings' => $this->element->settings);
    $settings = $this->element->settings + $form_state['element_settings'];

    $form['parameter'] = array(
      '#type' => 'container',
      '#tree' => TRUE,
      '#id' => 'rules-parameter-wrapper'
    );

    foreach($this->element->parameterInfo() as $name => $parameter) {
      if ($parameter['type'] == 'hidden') {
        continue;
      }

      $class = is_string($type = $parameter['type']) && isset($data_info[$type]['ui class']) ? $data_info[$type]['ui class'] : 'RulesDataUI';

      $form['parameter'][$name] = array(
        '#type' => 'fieldset',
        '#title' => check_plain($parameter['label']),
        '#description' => check_plain(isset($parameter['description']) ? $parameter['description'] : ''),
      );

      // Init the parameter input mode.
      $form_state['parameter_mode'][$name] = !isset($form_state['parameter_mode'][$name]) ? NULL : $form_state['parameter_mode'][$name];
      $form['parameter'][$name] += $this->getParameterForm($name, $parameter, $settings, $class, $form_state['parameter_mode'][$name]);
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
  protected function getParameterForm($name, $info, $settings, $class, &$mode) {
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
      $link_id = drupal_html_id('rules-parameter-switch-' . $name);

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
        '#prefix' => '<div class="rules_hidden rules_parameter_switch">',
        '#suffix' => '</div>',
        '#attached' => array(
          'js' => array(drupal_get_path('module', 'rules') . '/ui/rules.ui.js'),
          'css' => array(drupal_get_path('module', 'rules') . '/ui/rules.ui.css'),
        ),
        '#ajax' => array(
          'callback' => 'rules_ui_parameter_replace',
          'wrapper' => 'rules-parameter-wrapper',
          'event' => 'click',
          // Trigger as the Switch button.
          'trigger_as' => array(
            'name' => 'param_' . $name,
            'value' => t('Switch'),
          ),
        ),
      );
      $form['switch'] = array(
        '#type' => 'submit',
        '#name' => 'param_' . $name,
        '#parameter' => $name,
        '#value' => t('Switch'),
        '#submit' => array('rules_ui_parameter_replace_submit'),
        '#ajax' => array(
          'callback' => 'rules_ui_parameter_replace',
          'wrapper' => 'rules-parameter-wrapper',
        ),
        // Do not validate!
        '#limit_validation_errors' => array(),
      );
    }
    return $form;
  }

  /**
   * Implements RulesPluginUIInterface.
   */
  public function form_validate(&$form, &$form_state) {
    $this->form_extract_values($form, $form_state);

    if (!$this->element->access()) {
      form_set_error(NULL, t('Access violation!'));
    }
    try {
      $this->element->integrityCheck();
    }
    catch (RulesException $e) {
      //TODO: Add visual feedback which element had troubles.
      form_set_error('', $e->getMessage());
    }
    if (!empty($form['settings'])) {
      $this->settingsFormValidate($form, $form_state);
    }
  }

  /**
   * Applies the values of the form to the element.
   */
  function form_extract_values($form, $form_state) {
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
  public function form_submit(&$form, &$form_state) {
    if (!empty($form['settings'])) {
      $this->settingsFormSubmit($form, $form_state);
    }
    $this->element->save();
    // Clear the element id index map, so the map is regenerated in case of a
    // form rebuild. That way the map is indentical to the map that would be
    // generated on the next page load.
    self::clearElementMap($this->element->root()->map);
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
    );
    // TODO: Attach field form thus description and tags.
  }

  public function settingsFormExtractValues($form, &$form_state) {
    $this->element->label = $form_state['values']['label'];
  }

  public function settingsFormValidate($form, &$form_state) {

  }

  public function settingsFormSubmit($form, &$form_state) {

  }

  /**
   * Returns the name of class for the given data type.
   *
   * @param $dataType
   *  The name of the data typ
   * @return String
   */
  public function getDataTypeClass($dataType) {
    $data_info = rules_fetch_data('data_info');
    return (isset($data_info[$dataType]) ? $data_info[$dataType]['ui class'] : 'RulesDataUI');
  }

  /**
   * Returns the preview value of a parameter
   */
  public function getParameterValue($name) {
    if (isset($this->element->settings[$name . ':select'])) {
      $value = $this->element->settings[$name . ':select'];
    }
    else {
      $parameters = $this->element->parameterInfo();
      $type = $parameters[$name]['type'];
      $value = call_user_func(array($this->getDataTypeClass($type), 'previewData'), $this->element->settings[$name]);
    }
    return $value;
  }

  /**
   * Implements RulesPluginUIInterface.
   */
  public function buildContent() {
    $form['parameter'] = array(
      '#heading' => t('Parameter:'),
      '#theme' => 'rules_container_content',
    );
    foreach($this->element->parameterInfo() as $name => $parameter) {
      $form['parameter'][$name] = array(
        '#theme' => 'rules_parameter_content',
      );
      $form['parameter'][$name]['label'] = array(
        '#markup' => $parameter['label'],
      );
      $form['parameter'][$name]['value'] = array(
        '#markup' => $this->getParameterValue($name),
      );
    }
    return $form;
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
      'title' => t('Edit'),
      'href' => RulesPluginUI::path($name, 'edit', $element_id),
      'query' => drupal_get_destination(),
    );
    $render['#links']['delete'] = array(
      'title' => t('Delete'),
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
        $counter = 0;
        // Get the rules config.
        $element = $element->root();
        $element->map = array();
        $map =& $element->map;
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
  protected static function clearElementMap(&$map) {
    if (isset($map)) {
      foreach ($map as $element) {
        unset($element->childElementId);
      }
      unset($map);
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
      $path = RULES_UI_RULE_PATH . '/' . $name;
      $row = array();
      $row[] = l($config->label(), $path);
      // Add events if the configs are assigned to events.
      if ($config instanceof RulesTriggerableInterface) {
        $events = array();
        foreach ($config->events() as $event_name) {
          $events[] = $event_info[$event_name]['label'];
        }
        $row[] = implode(", ", $events);
      }

      $row[] = l(t('edit'), $path);
      $row[] = l(t('clone'), $path . '/clone', array('query' => drupal_get_destination()));
      $row[] = l(t('delete'), $path . '/delete', array('query' => drupal_get_destination()));
      $rows[] = $row;
    }
    $header = array(t('Label'), array('data' => t('Event')), array('data' => t('Operations'), 'colspan' => '3'));
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
    // Make the element available in the form state.
    $form_state['rules_element'] = $this->element;

    $form['elements'] = array(
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
      $label = $is_container ? check_plain($child->label()) : l($child->label(), RulesPluginUI::path($this->element->root()->name, 'edit', $id));

      $form['elements'][$id]['label']['value'] = array(
        '#markup' => $label,
      );
      $form['elements'][$id]['label']['content'] = $child->buildContent();
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
        '#value' => t('Save changes'),
        '#weight' => 10,
      );
    }
  }

  /**
   * Applies the values of the form to the given rule configuration.
   */
  function form_extract_values($form, $form_state) {
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
}

/**
 * UI for Rules condition container.
 */
class RulesConditionContainerUI extends RulesContainerPluginUI {

  public function form(&$form, &$form_state, $options = array(), $iterator = NULL) {
    parent::form($form, $form_state, $options, $iterator);
    // Add the add-* operation links.
    $form['add'] = self::addOperations();
    $form['elements']['#attributes']['class'][] = 'rules-condition-container';
    $form['elements']['#heading'] = t('Conditions');
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
    $form['elements']['#attributes']['class'][] = 'rules-action-container';
    $form['elements']['#heading'] = t('Actions');
  }
}

/**
 * Provides the interface used for implementing an abstract plugin by using
 * the Faces extension mechanism.
 */
interface RulesPluginImplInterface {

  public function execute();

  /**
   * Validates $settings independent from a form submission.
   *
   * @throws RulesException
   *   In case of validation errors, RulesExceptions are thrown.
   */
  public function validate();

  /**
   * Processes $settings independent from a form submission. Only successfully
   * validated settings are processed, so it can be also used to prepare
   * execution dependent on the settings.
   */
  public function process();

  /**
   * Checks whether the user has access to configure this element or rule
   * configuration.
   */
  public function access();

  /**
   * Alter the generated configuration form of the element.
   *
   * Validation and processing of the settings should be untied from the form
   * and implemented in validate() and process() wherever it makes sense.
   * For the remaining cases where form tied validation and processing is needed
   * make use of the form API #element_validate and #value_callback properties.
   */
  public function form_alter(&$form, $form_state);

}

/**
 * Class providing default implementations for the concrete plugin if not
 * overriden by the implementation.
 *
 * @see RulesPluginImplInterface
 */
class RulesAbstractPluginDefaults extends FacesExtender implements RulesPluginImplInterface {

  public function execute() {
    throw new RulesException($this->object->getPluginName() .": Execution implementation is missing.");
  }

  /**
   * Implements RulesPluginImplInterface.
   */
  public function access() {
    return TRUE;
  }

  public function validate() {}
  public function process() {}
  public function form_alter(&$form, $form_state) {}
}
