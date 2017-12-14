<?php

namespace Drupal\rules\Logger;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\rules\Event\SystemLoggerEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Dispatches new logger-items as SystemLoggerEvent.
 */
class RulesLog implements LoggerInterface {

  use RfcLoggerTrait;
  use DependencySerializationTrait;

  /**
   * The dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * Constructs a new instance.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   An EventDispatcherInterface instance.
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(EventDispatcherInterface $dispatcher, LogMessageParserInterface $parser) {
    $this->dispatcher = $dispatcher;
    $this->parser = $parser;
  }

  /**
   * {@inheritdoc}
   *
   * @todo: create a TypedData logger-entry object: https://www.drupal.org/node/2625238
   */
  public function log($level, $message, array $context = []) {
    // Remove any backtraces since they may contain an unserializable variable.
    unset($context['backtrace']);

    // Convert PSR3-style messages to SafeMarkup::format() style, so they can be
    // translated too in runtime.
    $message_placeholders = $this->parser->parseMessagePlaceholders($message, $context);

    $logger_entry = [
      'uid' => $context['uid'],
      'type' => $context['channel'],
      'message' => $message,
      'variables' => $message_placeholders,
      'severity' => $level,
      'link' => $context['link'],
      'location' => $context['request_uri'],
      'referer' => $context['referer'],
      'hostname' => $context['ip'],
      'timestamp' => $context['timestamp'],
    ];

    // Dispatch logger_entry event.
    $event = new SystemLoggerEvent($logger_entry, ['logger_entry' => $logger_entry]);
    $this->dispatcher->dispatch(SystemLoggerEvent::EVENT_NAME, $event);
  }

}
