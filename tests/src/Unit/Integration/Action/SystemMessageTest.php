<?php

namespace Drupal\Tests\rules\Unit\Integration\Action {

  use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

  /**
   * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\SystemMessage
   * @group RulesAction
   */
  class SystemMessageTest extends RulesIntegrationTestBase {

    /**
     * The action to be tested.
     *
     * @var \Drupal\rules\Core\RulesActionInterface
     */
    protected $action;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      parent::setUp();

      $this->action = $this->actionManager->createInstance('rules_system_message');

      // Clear the statically stored messages before every test run.
      $this->clearMessages();
    }

    /**
     * Tests the summary.
     *
     * @covers ::summary
     */
    public function testSummary() {
      $this->assertEquals('Show a message on the site', $this->action->summary());
    }

    /**
     * Tests the action execution.
     *
     * @covers ::execute
     */
    public function testActionExecution() {
      $this->action->setContextValue('message', 'test message')
        ->setContextValue('type', 'status')
        ->setContextValue('repeat', FALSE);

      // Execute the action multiple times. The message should still only
      // be stored once (repeat is set to FALSE).
      $this->action->execute();
      $this->action->execute();
      $this->action->execute();

      $messages = $this->getMessages('status');
      $this->assertNotNull($messages);
      $this->assertArrayEquals(['test message'], $messages);

      // Set the 'repeat' context to TRUE and execute the action again.
      $this->action->setContextValue('repeat', TRUE);
      $this->action->execute();

      // The message should be repeated now.
      $messages = $this->getMessages('status');
      $this->assertNotNull($messages);
      $this->assertArrayEquals(['test message', 'test message'], $messages);
    }

    /**
     * Tests that the action works if the optional repeat flag is not set.
     *
     * @covers ::execute
     */
    public function testOptionalRepeat() {
      $this->action->setContextValue('message', 'test message')
        ->setContextValue('type', 'status');

      $this->action->execute();

      $messages = $this->getMessages('status');
      $this->assertNotNull($messages);
      $this->assertArrayEquals(['test message'], $messages);
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
     * Retrieves the stored messages.
     *
     * @param null|string $type
     *   (optional) The type of messages to return. Defaults to NULL which
     *   causes all messages to be returned.
     *
     * @return array|null
     *   A multidimensional array with keys corresponding to the set message
     *   types. The indexed array values of each contain the set messages for
     *   that type. The messages returned are limited to the type specified in
     *   the $type parameter. If there are no messages of the specified type,
     *   an empty array is returned.
     */
    protected function getMessages($type = NULL) {
      $messages = drupal_set_message();
      if (isset($type)) {
        return isset($messages[$type]) ? $messages[$type] : NULL;
      }
      return $messages;
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
