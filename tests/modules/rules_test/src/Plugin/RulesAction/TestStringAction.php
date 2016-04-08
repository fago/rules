<?php

namespace Drupal\rules_test\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a test action that concatenates a string to itself.
 *
 * @RulesAction(
 *   id = "rules_test_string",
 *   label = @Translation("Test action string."),
 *   context = {
 *     "text" = @ContextDefinition("string",
 *       label = @Translation("Text to concatenate")
 *     )
 *   },
 *   configure_permissions = { "access test configuration" },
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
