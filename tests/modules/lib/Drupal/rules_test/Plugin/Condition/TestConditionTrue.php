<?php

/**
 * @file
 * Contains Drupal\rules_test\Plugin\Condition\TestConditionTrue.
 */

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\rules\RulesConditionInterface;

/**
 * Provides an always TRUE test condition.
 *
 * @Condition(
 *   id = "rules_test_condition_true",
 *   label = @Translation("Test condition returning true")
 * )
 */
class TestConditionTrue implements RulesConditionInterface {

  /**
   * Indicates whether this condition should be negated.
   *
   * @var bool
   */
  protected $negated = FALSE;

  public function evaluate() {
    
  }

  public function execute() {
    return TRUE;
  }

  public function negate() {
    $this->negated = TRUE;
    return $this;
  }

  public function isNegated() {
    return $this->negated;
  }

  public function getFormId() {

  }

  public function buildForm(array $form, array &$form_state) {

  }

  public function setExecutableManager(ExecutableManagerInterface $executableManager) {
    // We need to return ourselves here because ConditionManager::createInstance()
    // uses the return value of this function to return as plugin. Which is so
    // wrong and not specified on the interface!
    return $this;
  }

  public function submitForm(array &$form, array &$form_state) {

  }

  public function summary() {
    
  }

  public function validateForm(array &$form, array &$form_state) {

  }

}