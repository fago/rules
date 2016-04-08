<?php

namespace Drupal\rules\Ui;

/**
 * Trait for getting the rules_ui handler of the current request.
 *
 * Note that the current route must have the _rules_ui option set for the
 * handler being available. This is done automatically for routes generated for
 * the rules_ui (via \Drupal\rules\Routing\RulesUiRouteSubscriber).
 */
trait RulesUiHandlerTrait {

  /**
   * The rules UI handler.
   *
   * @var \Drupal\rules\Ui\RulesUiHandlerInterface
   */
  protected $rulesUiHandler;

  /**
   * Gets the rules UI handler of the current route.
   *
   * @return \Drupal\rules\Ui\RulesUiHandlerInterface|null
   *   The handler, or NULL if this is no rules_ui enabled route.
   */
  public function getRulesUiHandler() {
    if (!isset($this->rulesUiHandler)) {
      $this->rulesUiHandler = \Drupal::request()->attributes->get('rules_ui_handler');
    }
    return $this->rulesUiHandler;
  }

  /**
   * Sets the Rules UI handler.
   *
   * @param \Drupal\rules\Ui\RulesUiHandlerInterface $rules_ui_handler
   *   The Rules UI handler to set.
   */
  public function setRulesUiHandler(RulesUiHandlerInterface $rules_ui_handler) {
    $this->rulesUiHandler = $rules_ui_handler;
  }

}
