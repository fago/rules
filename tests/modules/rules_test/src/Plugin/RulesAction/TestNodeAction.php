<?php

namespace Drupal\rules_test\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a test action that sets a node title.
 *
 * @RulesAction(
 *   id = "rules_test_node",
 *   label = @Translation("Test action string."),
 *   context = {
 *     "node" = @ContextDefinition("entity:node",
 *       label = @Translation("Node to det the title on")
 *     ),
 *     "title" = @ContextDefinition("string",
 *       label = @Translation("New title that should be set")
 *     )
 *   }
 * )
 */
class TestNodeAction extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $node = $this->getContextValue('node');
    $title = $this->getContextValue('title');
    $node->setTitle($title);
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
    // The node where we changed the title should be auto-saved after the
    // execution.
    return ['node'];
  }

}
