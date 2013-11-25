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

  public function evaluate() {
    
  }

  public function execute() {
    return TRUE;
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
    
  }

  public function submitForm(array &$form, array &$form_state) {

  }

  public function summary() {
    
  }

  public function validateForm(array &$form, array &$form_state) {

  }

}