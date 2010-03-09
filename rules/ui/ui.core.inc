<?php
// $Id$

/**
 * @file Contains core ui functions.
 */

/**
 * Plugin UI Interface.
 */
interface RulesPluginUiInterface {

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
   *    - 'show elements': Whether to include the 'elements' fieldset for
   *      editing the actual configuration. Defaults to TRUE.
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
   * Checks whether the user has access to configure this element or rule
   * configuration.
   */
  public function access();

  /**
   * Returns ui operations for this element.
   * //TODO: allow modules to specify them in the plugin_info hook.
   */
  public function operations();
}

/**
 * Provides the interface used for implementing an abstract plugin by using
 * the Faces extension mechanism.
 */
interface RulesPluginImplInterface extends RulesPluginUiInterface {

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

  public function validate() {}
  public function process() {}
  public function form(&$form, &$form_state, $options = array()) {}
  public function form_validate(&$form, &$form_state) {}
  public function form_submit(&$form, &$form_state) {}

  public function access() {
    $info = $this->object->property('info');
    if (!empty($info['access callback']) && !call_user_func($info['access callback'], $this->property('itemName'), $this->object->getElementName())) {
      return FALSE;
    }
    foreach ($info['parameter'] as $name => $info) {
      if (isset($this->object->settings[$name . ':select']) && $wrapper = $this->call('applyDataSelector', array($this->object->settings[$name . ':select']))) {
        if ($wrapper->access('view') === FALSE) {
          return FALSE;
        }
        //TODO: Check input evaluator access.
      }
    }
    return TRUE;
  }

  public function operations() {}
  public function buildContent() {}
  public function help() {}
}
