<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\RulesAction.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Engine\RulesActionBase;
use Drupal\rules\Engine\RulesExpressionActionInterface;
use Drupal\rules\Engine\RulesExpressionBase;
use Drupal\rules\Engine\RulesState;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an executable action expression.
 *
 * This plugin is used to wrap action plugins and is responsible to setup all
 * the context necessary, instantiate the action plugin and to execute it.
 *
 * @RulesExpression(
 *   id = "rules_action",
 *   label = @Translation("An executable action.")
 * )
 */
class RulesAction extends RulesActionBase implements ContainerFactoryPluginInterface, RulesExpressionActionInterface {

  use RulesExpressionBase;

  /**
   * The action manager used to instantiate the action plugin.
   *
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   *   Contains the following entries:
   *   - action_id: The action plugin ID.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Action\ActionManager $actionManager
   *   The action manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ActionManager $actionManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->actionManager = $actionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('plugin.manager.action')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(RulesState $state) {
    $action = $this->actionManager->createInstance($this->configuration['action_id']);

    // We have to forward the context values from our configuration to the
    // action plugin.
    $this->mapContext($action, $state);

    $action->execute();

    // Now that the action has been executed it can provide additional
    // context which we will have to pass back in the evaluation state.
    $this->mapProvidedContext($action, $state);
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // @todo: Implement.
  }

}
