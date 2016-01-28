<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\IntegrityCheckTrait.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;
use Drupal\rules\Context\ContextDefinitionInterface;
use Drupal\rules\Context\ContextProviderInterface;
use Drupal\rules\Exception\RulesIntegrityException;

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
        try {
          $data_definition = $metadata_state->fetchDefinitionByPropertyPath($this->configuration['context_mapping'][$name]);
        }
        catch (RulesIntegrityException $e) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('Data selector %selector for context %context_name is invalid. @message', [
            '%selector' => $this->configuration['context_mapping'][$name],
            '%context_name' => $definition->getLabel(),
            '@message' => $e->getMessage(),
          ]));
          $violation->setContextName($name);
          $violation_list->add($violation);
        }

        if ($definition instanceof ContextDefinitionInterface
          && $definition->getAssignmentRestriction() === ContextDefinitionInterface::ASSIGNMENT_RESTRICTION_INPUT
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('The context %context_name may not be configured using a selector.', [
            '%context_name' => $definition->getLabel(),
          ]));
          $violation->setContextName($name);
          $violation_list->add($violation);
        }
      }
      elseif (isset($this->configuration['context_values'][$name])) {
        if ($definition instanceof ContextDefinitionInterface
          && $definition->getAssignmentRestriction() === ContextDefinitionInterface::ASSIGNMENT_RESTRICTION_SELECTOR
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('The context %context_name may only be configured using a selector.', [
            '%context_name' => $definition->getLabel(),
          ]));
          $violation->setContextName($name);
          $violation_list->add($violation);
        }
      }
    }

    if ($plugin instanceof ContextProviderInterface) {
      $provided_context_definitions = $plugin->getProvidedContextDefinitions();

      foreach ($provided_context_definitions as $name => $definition) {
        if (isset($this->configuration['provides_mapping'][$name])
          && !preg_match('/^[0-9a-zA-Z_]*$/', $this->configuration['provides_mapping'][$name])
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('Provided variable name %name contains not allowed characters.', [
            '%name' => $this->configuration['provides_mapping'][$name],
          ]));
          $violation->setContextName($name);
          $violation_list->add($violation);
        }
      }
    }

    return $violation_list;
  }

}
