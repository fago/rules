<?php

/**
 * @file
 * Contains \Drupal\rules_test\Plugin\Action\TestStringAction.
 */

namespace Drupal\rules_test\Plugin\Action;

use Drupal\rules\Engine\RulesActionBase;
use Drupal\rules\Engine\RulesLog;

/**
 * Provides a test action that concatenates a string to itself.
 *
 * @Action(
 *   id = "rules_test_string",
 *   label = @Translation("Test action string."),
 *   context = {
 *     "text" = @ContextDefinition("string",
 *       label = @Translation("Text to concatenate")
 *     )
 *   },
 *   provides = {
 *     "concatenated" = @ContextDefinition("string",
 *       label = @Translation("Concatenated result")
 *     )
 *   }
 * )
 */
class TestStringAction extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $text = $this->getContextValue('text');
    $this->setProvidedValue('concatenated', $text . $text);
  }

}
