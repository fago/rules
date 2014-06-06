<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\RulesAction.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Engine\RulesActionBase;
use Drupal\rules\Engine\RulesActionInterface;
use Drupal\rules\Engine\RulesExpressionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an executable action expression.
 *
 * This plugin is used to wrap action plugins and is responsible to setup all
 * the context necessary, instantiate the action plugin and to execute it.
 *
 * @RulesExpression(
 *   id = "rules_action",
 *   label = @Translation("An executable action.")
 * )
 */
class RulesAction extends RulesActionBase implements RulesActionInterface, ContainerFactoryPluginInterface, RulesExpressionInterface {

  /**
   * The action manager used to instantiate the action plugin.
   *
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * Constructs an RulesAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   *   Contains the following entries:
   *   - action_id: The action plugin ID.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   * @param \Drupal\Core\Action\ActionManager $actionManager
   *   The action manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TypedDataManager $typed_data_manager, ActionManager $actionManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $typed_data_manager);

    $this->actionManager = $actionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('typed_data_manager'),
      $container->get('plugin.manager.action')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $action = $this->actionManager->createInstance($this->configuration['action_id']);
    // @todo context mapping will happen here, we have to forward the context
    // definitions from our plugin configuration to the action plugin.
    $action->execute();
    // @todo Now that the action has been executed it can provide additional
    // context which we will have to pass back to any parent expression.
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // @todo: Implement.
  }

}
