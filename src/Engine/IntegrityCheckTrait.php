<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\IntegrityCheckTrait.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;

/**
 * Provides shared integrity checking methods for conditions and actions.
 */
trait IntegrityCheckTrait {

  /**
   * Performs the integrity check.
   *
   * @param CoreContextAwarePluginInterface $plugin
   *   The plugin with its defined context.
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The current configuration state with all defined variables that are
   *   available.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationList
   *   The list of integrity violations.
   */
  protected function doCheckIntegrity(CoreContextAwarePluginInterface $plugin, ExecutionMetadataStateInterface $metadata_state) {
    $violation_list = new IntegrityViolationList();
    $context_definitions = $plugin->getContextDefinitions();
    foreach ($context_definitions as $name => $definition) {
      // Check if a data selector is configured that maps to the state.
      if (isset($this->configuration['context_mapping'][$name])) {
        $data_definition = $metadata_state->applyDataSelector($this->configuration['context_mapping'][$name]);

        if ($data_definition === NULL) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('Data selector %selector for context %context_name is invalid.', [
            '%selector' => $this->configuration['context_mapping'][$name],
            '%context_name' => $definition->getLabel(),
          ]));
          $violation->setContextName($name);
          $violation_list->add($violation);
        }
      }
    }

    return $violation_list;
  }

}
