<?php

namespace Drupal\rules\Logger;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannel;
use Psr\Log\LoggerTrait;

/**
 * Logs rules log entries in the available loggers.
 */
class RulesLoggerChannel extends LoggerChannel {
  use LoggerTrait;

  /**
   * A configuration object with rules settings.
   *
   * @var ImmutableConfig
   */
  protected $config;

  /**
   * Static storage of log entries.
   *
   * @var array
   */
  protected $logs = [];

  /**
   * Creates RulesLoggerChannel object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct('rules');
    $this->config = $config_factory->get('rules.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logs[] = [
      'level' => $level,
      'message' => $message,
      'context' => $context,
    ];

    // Log message only if rules logging setting is enabled.
    if ($this->config->get('log')) {
      if ($this->levelTranslation[$this->config->get('log_level_system')] >= $this->levelTranslation[$level]) {
        parent::log($level, $message, $context);
      }
    }
    if ($this->config->get('debug_screen')) {
      if ($this->levelTranslation[$this->config->get('log_level_screen')] >= $this->levelTranslation[$level]) {
        drupal_set_message($message, $level);
      }
    }
  }

  /**
   * Returns the structured array of entries.
   *
   * @return array
   *   Array of stored log entries.
   */
  public function getLogs() {
    return $this->logs;
  }

  /**
   * Clears the static logs entries cache.
   */
  public function clearLogs() {
    $this->logs = [];
  }

}
