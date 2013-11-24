<?php

/**
 * @file
 * Contains Drupal\rules_test\Plugin\Condition\TestConditionTrue.
 */

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\Core\Condition\ConditionInterface;

/**
 * Provides an always TRUE test condition.
 *
 * @Condition(
 *   id = "rules_test_condition_true",
 *   label = @Translation("Test condition returning true")
 * )
 */
class TestConditionTrue implements ConditionInterface {
  public function buildForm(array $form, array &$form_state) {

  }

  public function evaluate() {
    
  }

  public function execute() {
    return TRUE;
  }

  public function getFormId() {
    
  }

  public function isNegated() {

  }

  public function setExecutableManager(\Drupal\Core\Executable\ExecutableManagerInterface $executableManager) {
    
  }

  public function submitForm(array &$form, array &$form_state) {

  }

  public function summary() {
    
  }

  public function validateForm(array &$form, array &$form_state) {

  }

}