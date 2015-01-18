<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\SendAccountEmailTest.
 */

namespace Drupal\Tests\rules\Integration\Action {

  use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

  /**
   * @coversDefaultClass \Drupal\rules\Plugin\Action\SendAccountEmail
   * @group rules_actions
   */
  class SendAccountEmailTest extends RulesEntityIntegrationTestBase {

    /**
     * The action to be tested.
     *
     * @var \Drupal\rules\Engine\RulesActionInterface
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
     * @covers ::summary()
     */
    public function testSummary() {
      $this->assertEquals('Send account e-mail', $this->action->summary());
    }

    /**
     * Tests the action execution.
     *
     * @covers ::execute()
     */
    public function testActionExecution() {
      $account = $this->getMock('Drupal\user\UserInterface');
      $mail_type = 'test_mail_type';
      $this->action->setContextValue('user', $account)
        ->setContextValue('email_type', $mail_type);

      // We instantiate the global mock object which will be used to call the
      // userMailNotifyCall() method from the _user_mail_notify() in the global
      // namespace, function which we define below.
      global $user_mail_notify_test_helper;
      $user_mail_notify_test_helper = $this->getMock('Drupal\Tests\rules\Integration\Action\UserMailNotifyTestHelper');

      $user_mail_notify_test_helper->expects($this->once())
        ->method('userMailNotifyCall')
        ->with($mail_type, $account);

      $this->action->execute();
    }

  }

  /**
   * A helper class to check when the _user_mail_notify() is called.
   *
   * When testing the action, we want to check if the _user_mail_notify()
   * global function is called. We can easy test if a method of a mock object
   * is called, but not so easy if a php function is called. And also, the
   * _user_mail_notify() function is not available when running the test case
   * because the user module is not enabled. To check if the _user_mail_notify()
   * gets called we declare it below in the global scope, and what we do is
   * to just call the userMailNotifyCall() method of an UserMailNotifyTestHelper
   * object (it is actually a globally available mock object).
   *
   * @package Drupal\Tests\rules\Integration\Action
   */
  class UserMailNotifyTestHelper {

    public function userMailNotifyCall($op, $account, $langcode = NULL) {
      // If this function gets called in this namespace, it means that the
      // _user_mail_notify() was called.
    }
  }
}

namespace {

  if (!function_exists('_user_mail_notify')) {
    function _user_mail_notify($op, $account, $langcode = NULL) {
      global $user_mail_notify_test_helper;
      $user_mail_notify_test_helper->userMailNotifyCall($op, $account, $langcode);
    }
  }
}
