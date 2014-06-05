<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\ActionSet.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Action\ActionInterface;
use Drupal\rules\Engine\RulesActionContainerInterface;
use Drupal\rules\Engine\RulesExpressionInterface;

/**
 * Holds a set of actions and executes all of them.
 *
 * @RulesExpression(
 *   id = "rules_action_set",
 *   label = @Translation("Action set")
 * )
 */
class ActionSet extends PluginBase implements RulesActionContainerInterface, RulesExpressionInterface {

  /**
   * List of actions that will be executed.
   *
   * @var \Drupal\Core\Action\ActionInterface[]
   */
  protected $actions = [];

  /**
   * {@inheritdoc}
   */
  public function addAction(ActionInterface $action) {
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
