<?php

/**
 * @file
 * Contains Drupal\rules_test\Plugin\Condition\TestConditionFalse.
 */

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\rules\RulesConditionInterface;

/**
 * Provides an always FALSE test condition.
 *
 * @Condition(
 *   id = "rules_test_condition_false",
 *   label = @Translation("Test condition returning false")
 * )
 */
class TestConditionFalse implements RulesConditionInterface {

  public function evaluate() {

  }

  public function execute() {
    return FALSE;
  }

  public function negate() {
    return $this;
  }

  public function isNegated() {

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
