<?php

/**
 * @file
 * Contains \Drupal\rules_test\Plugin\Action\TestAction.
 */

namespace Drupal\rules_test\Plugin\Action;

use Drupal\rules\Core\RulesActionBase;
use Drupal\rules\Engine\RulesLog;

/**
 * Provides an action writing something to the Rules log.
 *
 * @Action(
 *   id = "rules_test_log",
 *   label = @Translation("Test action logging.")
 * )
 */
class TestLogAction extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    RulesLog::logger()->log('action called');
  }

}
