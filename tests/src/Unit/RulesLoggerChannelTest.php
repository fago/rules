<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\LoggerChannelTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\rules\Logger\RulesLoggerChannel;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LogLevel;

/**
 * @coversDefaultClass \Drupal\rules\Logger\RulesLoggerChannel
 * @group Logger
 */
class RulesLoggerChannelTest extends UnitTestCase {

  /**
   * Tests LoggerChannel::log().
   *
   * @param string $psr3_message_level
   *   Expected PSR3 log level.
   * @param int $rfc_message_level
   *   Expected RFC 5424 log level.
   * @param int $debug_log
   *   Is logging enabled.
   * @param string $psr3_log_error_level
   *   Allowed PSR3 log level.
   * @param int $expect_log
   *   Amount of logs to be created
   * @param string $message
   *   Log message.
   *
   * @dataProvider providerTestLog
   *
   * @covers ::log
   */
  public function testLog($psr3_message_level, $rfc_message_level, $debug_log, $psr3_log_error_level, $expect_log, $message) {
    $config = $this->getConfigFactoryStub([
      'rules.settings' => [
        'debug_log' => $debug_log,
        'log_errors' => $psr3_log_error_level,
      ],
    ]);
    $channel = new RulesLoggerChannel($config);
    $logger = $this->getMock('Psr\Log\LoggerInterface');
    $channel->addLogger($logger);

    $logger->expects($this->exactly($expect_log))
      ->method('log')
      ->with($rfc_message_level, $message);

    $channel->log($psr3_message_level, $message);
  }

  /**
   * Data provider for self::testLog().
   */
  public function providerTestLog() {
    return [
      [LogLevel::DEBUG, RfcLogLevel::DEBUG, 0, LogLevel::DEBUG, 0, 'apple'],
      [LogLevel::CRITICAL, RfcLogLevel::CRITICAL, 1, LogLevel::DEBUG, 1, 'banana'],
      [LogLevel::CRITICAL, RfcLogLevel::CRITICAL, 1, LogLevel::DEBUG, 1, 'orange'],
      [LogLevel::INFO, RfcLogLevel::INFO, 1, LogLevel::CRITICAL, 0, 'cucumber'],
    ];
  }

}
