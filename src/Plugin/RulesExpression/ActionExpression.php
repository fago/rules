<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\ActionExpression.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
class ActionExpression extends PluginBase implements ActionInterface, ContainerFactoryPluginInterface {

  /**
   * The action manager used to instantiate the action plugin.
   *
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * The action plugin ID used to execute.
   *
   * @var string
   */
  protected $actionId;

  /**
   * Constructs an ActionExpression object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Action\ActionManager $actionManager
   *   The action manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, \Drupal\Core\Action\ActionManager $actionManager) {
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

  public function setActionPluginId($id) {
    $this->actionId = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $action = $this->actionManager->createInstance($this->actionId);
    // @todo context mapping will happen here.
    $action->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // @todo: Implement.
  }

}
