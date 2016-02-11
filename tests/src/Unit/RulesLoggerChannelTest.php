<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\LoggerChannelTest.
 */

namespace Drupal\Tests\rules\Unit {

  use Drupal\Core\Logger\RfcLogLevel;
  use Drupal\rules\Logger\RulesLoggerChannel;
  use Drupal\Tests\UnitTestCase;
  use Prophecy\Argument;
  use Psr\Log\LoggerInterface;
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
     * @param int $log
     *   Is system logging enabled.
     * @param int $debug_screen
     *   Is screen logging enabled.
     * @param string $psr3_log_error_level
     *   Allowed PSR3 log level.
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
          LogLevel::DEBUG,
          RfcLogLevel::DEBUG,
          0,
          0,
          LogLevel::DEBUG,
          0,
          0,
          'apple',
        ],
        [
          LogLevel::DEBUG,
          RfcLogLevel::DEBUG,
          0,
          1,
          LogLevel::DEBUG,
          0,
          1,
          'pear',
        ],
        [
          LogLevel::CRITICAL,
          RfcLogLevel::CRITICAL,
          1,
          0,
          LogLevel::DEBUG,
          1,
          0,
          'banana',
        ],
        [
          LogLevel::CRITICAL,
          RfcLogLevel::CRITICAL,
          1,
          1,
          LogLevel::DEBUG,
          1,
          1,
          'carrot',
        ],
        [
          LogLevel::CRITICAL,
          RfcLogLevel::CRITICAL,
          1,
          0,
          LogLevel::DEBUG,
          1,
          0,
          'orange',
        ],
        [
          LogLevel::CRITICAL,
          RfcLogLevel::CRITICAL,
          1,
          1,
          LogLevel::DEBUG,
          1,
          1,
          'kumkwat',
        ],
        [
          LogLevel::INFO,
          RfcLogLevel::INFO,
          1,
          0,
          LogLevel::CRITICAL,
          0,
          0,
          'cucumber',
        ],
        [
          LogLevel::INFO,
          RfcLogLevel::INFO,
          1,
          1,
          LogLevel::CRITICAL,
          0,
          0,
          'dragonfruit',
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
