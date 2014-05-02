<?php

/**
 * @file
 * Contains Drupal\rules\Engine\RulesConditionContainer.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionPluginBase;

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
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   The condition object.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\Rule
   *   The current rule object for chaining.
   */
  public function condition(ConditionInterface $condition) {
    $this->conditions[] = $condition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    if (isset($this->executableManager)) {
      return $this->executableManager->execute($this);
    }
    $result = $this->evaluate();
    return $this->isNegated() ? !$result : $result;
  }

  /**
   * {@inheritdoc}
   */
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // @todo: Move to and implement at inheriting classes.
  }

}
