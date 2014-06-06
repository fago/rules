<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\ActionSet.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\RulesActionBase;
use Drupal\rules\Engine\RulesActionContainerInterface;
use Drupal\rules\Engine\RulesActionInterface;
use Drupal\rules\Engine\RulesExpressionInterface;

/**
 * Holds a set of actions and executes all of them.
 *
 * @RulesExpression(
 *   id = "rules_action_set",
 *   label = @Translation("Action set")
 * )
 */
class ActionSet extends RulesActionBase implements RulesActionContainerInterface, RulesExpressionInterface {

  /**
   * List of actions that will be executed.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface[]
   */
  protected $actions = [];

  /**
   * {@inheritdoc}
   */
  public function addAction(RulesActionInterface $action) {
    $this->actions[] = $action;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    foreach ($this->actions as $action) {
      $action->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // @todo Revisit if we actually want this method.
  }

}
