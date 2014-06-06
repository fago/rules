<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesConditionContainer.
 */

namespace Drupal\rules\Engine;

/**
 * Container for conditions.
 */
abstract class RulesConditionContainer extends RulesConditionBase implements RulesConditionContainerInterface {

  /**
   * List of conditions that are evaluated.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface[]
   */
  protected $conditions = [];

  /**
   * {@inheritdoc}
   */
  public function addCondition(RulesConditionInterface $condition) {
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
