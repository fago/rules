<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\UserBlock.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides "Block User" action.
 *
 * @Action(
 *   id = "rules_user_block",
 *   label = @Translation("Block a user"),
 *   category = @Translation("User"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User"),
 *       description = @Translation("Specifies the user, that should be blocked.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class UserBlock extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Constructs a UserBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SessionManagerInterface $session_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->sessionManager = $session_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('session_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Block a user');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    /**
     * @var $user \Drupal\user\UserInterface
     */
    $user = $this->getContextValue('user');

    // Do nothing if user is anonymous or already blocked.
    if ($user->isAuthenticated() && $user->isActive()) {
      $user->block();
      $this->sessionManager->delete($user->id());
    }
  }

}
