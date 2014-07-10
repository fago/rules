<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\ActionSet.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\RulesActionBase;
use Drupal\rules\Engine\RulesActionContainerInterface;
use Drupal\rules\Engine\RulesExpressionActionInterface;
use Drupal\rules\Engine\RulesExpressionBase;
use Drupal\rules\Engine\RulesState;

/**
 * Holds a set of actions and executes all of them.
 *
 * @RulesExpression(
 *   id = "rules_action_set",
 *   label = @Translation("Action set")
 * )
 */
class ActionSet extends RulesActionBase implements RulesActionContainerInterface {

  use RulesExpressionBase;

  /**
   * List of actions that will be executed.
   *
   * @var \Drupal\rules\Engine\RulesExpressionActionInterface[]
   */
  protected $actions = [];

  /**
   * {@inheritdoc}
   */
  public function addAction(RulesExpressionActionInterface $action) {
    $this->actions[] = $action;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(RulesState $state) {
    foreach ($this->actions as $action) {
      $action->executeWithState($state);
    }
  }

}
