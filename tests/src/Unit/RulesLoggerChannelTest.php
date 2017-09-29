<?php

namespace Drupal\Tests\rules\Unit {

  use Drupal\Core\Logger\RfcLogLevel;
  use Drupal\rules\Logger\RulesLoggerChannel;
  use Drupal\Tests\UnitTestCase;
  use Prophecy\Argument;
  use Psr\Log\LoggerInterface;
  use Psr\Log\LogLevel;

  /**
   * @coversDefaultClass \Drupal\rules\Logger\RulesLoggerChannel
   * @group Rules
   */
  class RulesLoggerChannelTest extends UnitTestCase {

    /**
     * Tests LoggerChannel::log().
     *
     * @param string $psr3_message_level
     *   Expected PSR3 log level.
     * @param int $rfc_message_level
     *   Expected RFC 5424 log level.
     * @param int $log
     *   Is system logging enabled.
     * @param int $debug_screen
     *   Is screen logging enabled.
     * @param string $psr3_log_error_level
     *   Minimum required PSR3 log level at which to log.
     * @param int $expect_system_log
     *   Amount of logs to be created.
     * @param int $expect_screen_log
     *   Amount of messages to be created.
     * @param string $message
     *   Log message.
     *
     * @dataProvider providerTestLog
     *
     * @covers ::log
     */
    public function testLog($psr3_message_level, $rfc_message_level, $log, $debug_screen, $psr3_log_error_level, $expect_system_log, $expect_screen_log, $message) {
      $this->clearMessages();

      $config = $this->getConfigFactoryStub([
        'rules.settings' => [
          'log' => $log,
          'debug_screen' => $debug_screen,
          'log_level_system' => $psr3_log_error_level,
          'log_level_screen' => $psr3_log_error_level,
        ],
      ]);
      $channel = new RulesLoggerChannel($config);
      $logger = $this->prophesize(LoggerInterface::class);
      $logger->log($rfc_message_level, $message, Argument::type('array'))
        ->shouldBeCalledTimes($expect_system_log);

      $channel->addLogger($logger->reveal());

      $channel->log($psr3_message_level, $message);

      $messages = drupal_set_message();
      if ($expect_screen_log > 0) {
        $this->assertNotNull($messages);
        $this->assertArrayEquals([$psr3_message_level => [$message]], $messages);
      }
      else {
        $this->assertNull($messages);
      }
    }

    /**
     * Clears the statically stored messages.
     *
     * @param null|string $type
     *   (optional) The type of messages to clear. Defaults to NULL which causes
     *   all messages to be cleared.
     *
     * @return $this
     */
    protected function clearMessages($type = NULL) {
      $messages = &drupal_set_message();
      if (isset($type)) {
        unset($messages[$type]);
      }
      else {
        $messages = NULL;
      }
      return $this;
    }

    /**
     * Data provider for self::testLog().
     */
    public function providerTestLog() {
      return [
        [
          'psr3_message_level' => LogLevel::DEBUG,
          'rfc_message_level' => RfcLogLevel::DEBUG,
          'system_log_enabled' => 0,
          'screen_log_enabled' => 0,
          'min_psr3_level' => LogLevel::DEBUG,
          'expected_system_logs' => 0,
          'expected_screen_logs' => 0,
          'message' => 'apple',
        ],
        [
          'psr3_message_level' => LogLevel::DEBUG,
          'rfc_message_level' => RfcLogLevel::DEBUG,
          'system_log_enabled' => 0,
          'screen_log_enabled' => 1,
          'min_psr3_level' => LogLevel::DEBUG,
          'expected_system_logs' => 0,
          'expected_screen_logs' => 1,
          'message' => 'pear',
        ],
        [
          'psr3_message_level' => LogLevel::CRITICAL,
          'rfc_message_level' => RfcLogLevel::CRITICAL,
          'system_log_enabled' => 1,
          'screen_log_enabled' => 0,
          'min_psr3_level' => LogLevel::DEBUG,
          'expected_system_logs' => 1,
          'expected_screen_logs' => 0,
          'message' => 'banana',
        ],
        [
          'psr3_message_level' => LogLevel::CRITICAL,
          'rfc_message_level' => RfcLogLevel::CRITICAL,
          'system_log_enabled' => 1,
          'screen_log_enabled' => 1,
          'min_psr3_level' => LogLevel::DEBUG,
          'expected_system_logs' => 1,
          'expected_screen_logs' => 1,
          'message' => 'carrot',
        ],
        [
          'psr3_message_level' => LogLevel::CRITICAL,
          'rfc_message_level' => RfcLogLevel::CRITICAL,
          'system_log_enabled' => 1,
          'screen_log_enabled' => 0,
          'min_psr3_level' => LogLevel::DEBUG,
          'expected_system_logs' => 1,
          'expected_screen_logs' => 0,
          'message' => 'orange',
        ],
        [
          'psr3_message_level' => LogLevel::CRITICAL,
          'rfc_message_level' => RfcLogLevel::CRITICAL,
          'system_log_enabled' => 1,
          'screen_log_enabled' => 1,
          'min_psr3_level' => LogLevel::DEBUG,
          'expected_system_logs' => 1,
          'expected_screen_logs' => 1,
          'message' => 'kumkwat',
        ],
        [
          'psr3_message_level' => LogLevel::INFO,
          'rfc_message_level' => RfcLogLevel::INFO,
          'system_log_enabled' => 1,
          'screen_log_enabled' => 0,
          'min_psr3_level' => LogLevel::CRITICAL,
          'expected_system_logs' => 0,
          'expected_screen_logs' => 0,
          'message' => 'cucumber',
        ],
        [
          'psr3_message_level' => LogLevel::INFO,
          'rfc_message_level' => RfcLogLevel::INFO,
          'system_log_enabled' => 1,
          'screen_log_enabled' => 1,
          'min_psr3_level' => LogLevel::CRITICAL,
          'expected_system_logs' => 0,
          'expected_screen_logs' => 0,
          'message' => 'dragonfruit',
        ],
      ];
    }

  }
}

namespace {

  if (!function_exists('drupal_set_message')) {

    /**
     * Dummy replacement for testing.
     */
    function &drupal_set_message($message = NULL, $type = 'status', $repeat = FALSE) {
      static $messages = NULL;

      if (!empty($message)) {
        $messages[$type] = isset($messages[$type]) ? $messages[$type] : [];
        if ($repeat || !in_array($message, $messages[$type])) {
          $messages[$type][] = $message;
        }
      }

      return $messages;
    }

  }
}
