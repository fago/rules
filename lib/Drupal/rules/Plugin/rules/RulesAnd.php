<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\rules\RulesAnd.
 */

namespace Drupal\rules\Plugin\rules;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionPluginBase;

/**
 * Container for consitions and actions.
 *
 * @Rules(
 *   id = "rules_and",
 *   label = @Translation("A logical And condition")
 * )
 */
class RulesAnd extends ConditionPluginBase {

  /**
   * List of conditions that are evaluated.
   *
   * @var array
   */
  protected $conditions = array();

  /**
   * Add a condition.
   *
   * @param ConditionInterface2 $condition
   *   The condition object.
   *
   * @return \Drupal\rules\Plugin\Action\Rule
   *   The current rule object for chaining.
   */
  public function condition(ConditionInterface $condition) {
    $this->conditions[] = $condition;
    return $this;
  }

  public function buildForm(array $form, array &$form_state) {

  }

  public function evaluate() {

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

  public function execute() {
    foreach ($this->conditions as $condition) {
      if (!$condition->execute() && !$condition->isNegated()) {
        return FALSE;
      }
    }
    // An empty AND should return FALSE, otherwise all conditions evaluated to
    // TRUE and we return TRUE.
    return !empty($this->conditions);
  }

}
