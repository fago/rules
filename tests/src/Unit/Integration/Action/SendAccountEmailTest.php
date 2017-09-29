<?php

namespace Drupal\Tests\rules\Unit\Integration\Action {

  use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
  use Drupal\user\UserInterface;

  /**
   * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\SendAccountEmail
   * @group RulesAction
   */
  class SendAccountEmailTest extends RulesEntityIntegrationTestBase {

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

      $this->enableModule('user');
      $this->action = $this->actionManager->createInstance('rules_send_account_email');
    }

    /**
     * Tests the summary.
     *
     * @covers ::summary
     */
    public function testSummary() {
      $this->assertEquals('Send account e-mail', $this->action->summary());
    }

    /**
     * Tests the action execution.
     *
     * @covers ::execute
     */
    public function testActionExecution() {
      $account = $this->prophesizeEntity(UserInterface::class);
      $mail_type = 'test_mail_type';
      $this->action->setContextValue('user', $account->reveal())
        ->setContextValue('email_type', $mail_type);

      $this->action->execute();

      // To ge the notifications that were sent, we call the _user_mail_notify()
      // with no parameters.
      $notifications = _user_mail_notify();
      $this->assertSame([$mail_type => 1], $notifications);
    }

  }

}

namespace {

  /*
   * We fake the _user_mail_notify() when using unit tests and we adapt it so
   * that we can get how many times the function was called with a specific $op.
   */
  if (!function_exists('_user_mail_notify')) {

    /**
     * Dummy replacement for testing.
     */
    function _user_mail_notify($op = NULL, $account = NULL, $langcode = NULL) {
      static $notifications_sent;
      if (!empty($op)) {
        if (!isset($notifications_sent[$op])) {
          $notifications_sent[$op] = 0;
        }
        $notifications_sent[$op]++;
      }
      return $notifications_sent;
    }

  }
}
