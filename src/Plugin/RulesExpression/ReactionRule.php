<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\ReactionRule.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\ExpressionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\rules\Engine\RulesEventManager;

/**
 * Provides a reaction rule that can be configured with an event context.
 *
 * @RulesExpression(
 *   id = "rules_reaction_rule",
 *   label = @Translation("A reaction rule triggering on events")
 * )
 */
class ReactionRule extends Rule {

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\rules\Engine\ExpressionManagerInterface $expression_manager
   *   The rules expression plugin manager.
   * @param \Drupal\rules\Engine\RulesEventManager $event_manager
   *   The Rules event manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExpressionManagerInterface $expression_manager, RulesEventManager $event_manager) {
    // @todo Reaction rules should also work with multiple events.
    if (isset($configuration['event'])) {
      $event_definition = $event_manager->getDefinition($configuration['event']);
      if (!empty($event_definition['context'])) {
        $plugin_definition['context'] = $event_definition['context'];
      }
    }

    parent::__construct($configuration, $plugin_id, $plugin_definition, $expression_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.rules_expression'),
      $container->get('plugin.manager.rules_event')
    );
  }

}
