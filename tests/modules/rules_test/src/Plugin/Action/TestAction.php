<?php

/**
 * @file
 * Contains \Drupal\rules_test\Plugin\Action\TestAction.
 */

namespace Drupal\rules_test\Plugin\Action;

use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\rules\Engine\RulesLog;

/**
 * Provides an action writing something to the Rules log.
 *
 * @Action(
 *   id = "rules_test_log",
 *   label = @Translation("Test action logging.")
 * )
 */
class TestAction extends PluginBase implements ActionInterface {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    RulesLog::logger()->log('action called');
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // @todo: Implement in parent and remove.
  }

}
