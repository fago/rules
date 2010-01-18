<?php
// $Id$

/**
 * @file Contains core ui functions.
 */

/**
 * Plugin UI Interface.
 */
interface RulesPluginUiInterface {

  public function form();
  public function form_validate();
  public function form_submit();
  public function render();
  public function help();

  /**
   * Checks whether the user has access to configure this plugin.
   */
  public function access();

  /**
   * Returns ui operations for this plugin.
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
class RulesAbstractPluginDefaults extends FacesExtender {

  public function implementsFaces() {
    return array('RulesPluginImplInterface');
  }

  public function execute($element) {
    throw new RulesException($element->getPluginName() .": Execution implementation is missing.");
  }

  public function validate($element) {}
  public function process($element) {}
  public function form($element) {}
  public function form_validate($element) {}
  public function form_submit($element) {}

  public function access($element) {
    $info = $element->property('info');
    if (!empty($info['access callback']) && !call_user_func($info['access callback'], $element->property('itemName'), $element->getElementName())) {
      return FALSE;
    }
    foreach ($info['parameter'] as $name => $info) {
      if (isset($element->settings[$name . ':select']) && $wrapper = $element->call('applyDataSelector', array($element->settings[$name . ':select']))) {
        if ($wrapper->access('view') === FALSE) {
          return FALSE;
        }
        //TODO: Check input evaluator access.
      }
    }
    return TRUE;
  }

  public function operations($element) {}
  public function render($element) {}
  public function help($element) {}
}
