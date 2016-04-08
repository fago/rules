<?php

namespace Drupal\rules_test\Plugin\RulesAction;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\rules\Logger\RulesLoggerChannel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an action writing something to the Rules log.
 *
 * @RulesAction(
 *   id = "rules_test_log",
 *   label = @Translation("Test action logging."),
 *   context = {
 *     "message" = @ContextDefinition("string",
 *       label = @Translation("Message to log"),
 *       required = false
 *     )
 *   }
 * )
 */
class TestLogAction extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * Rules logger instance.
   *
   * @var \Drupal\rules\Logger\RulesLoggerChannel
   */
  protected $logger;

  /**
   * Constructs a TestLogAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\rules\Logger\RulesLoggerChannel $logger
   *   Rules logger object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RulesLoggerChannel $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.rules')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $message = $this->getContextValue('message');
    if (empty($message)) {
      $message = 'action called';
    }
    $this->logger->info($message);
  }

}
