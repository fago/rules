<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\rules\RulesConditionContainer.
 */

namespace Drupal\rules\Plugin\rules;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\rules\RulesConditionInterface;

/**
 * Container for conditions.
 */
abstract class RulesConditionContainer extends ConditionPluginBase implements RulesConditionInterface {

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

  public function setExecutableManager(ExecutableManagerInterface $executableManager) {
    // We need to return ourselves here because ConditionManager::createInstance()
    // uses the return value of this function to return as plugin. Which is so
    // wrong and not specified on the interface!
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
    return $this;
  }

  public function summary() {

  }

}
