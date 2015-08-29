<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\SystemSendEmailTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\SystemSendEmail
 * @group rules_actions
 */
class SystemSendEmailTest extends RulesIntegrationTestBase {

  /**
   * @var \Psr\Log\LoggerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $mailManager;

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Plugin\RulesAction\SystemSendEmail
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->logger = $this->prophesize(LoggerInterface::class);

    $this->mailManager = $this->prophesize(MailManagerInterface::class);

    $this->container->set('logger.factory', $this->logger->reveal());
    $this->container->set('plugin.manager.mail', $this->mailManager->reveal());

    $this->action = $this->actionManager->createInstance('rules_send_email');
  }


  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Send email', $this->action->summary());
  }

  /**
   * Tests sending a mail to one recipient.
   *
   * @covers ::execute
   */
  public function testSendMailToOneRecipient() {
    $to = ['mail@example.com'];
    $this->action->setContextValue('to', $to)
      ->setContextValue('subject', 'subject')
      ->setContextValue('message', 'hello');

    $params = [
      'subject' => 'subject',
      'message' => 'hello',
    ];

    $this->mailManager->mail(
      'rules', 'rules_action_mail_' . $this->action->getPluginId(),
      implode(', ', $to),
      LanguageInterface::LANGCODE_SITE_DEFAULT,
      $params,
      NULL
    )
      ->willReturn(['result' => TRUE])
      ->shouldBeCalledTimes(1);

    $this->logger->log(
      LogLevel::NOTICE,
      SafeMarkup::format('Successfully sent email to %to', ['%to' => implode(', ', $to)])
    )->shouldBeCalledTimes(1);

    $this->action->execute();
  }

  /**
   * Tests sending a mail to two recipients.
   *
   * @covers ::execute
   */
  public function testSendMailToTwoRecipients() {
    $to = ['mail@example.com', 'mail2@example.com'];
    $this->action->setContextValue('to', $to)
      ->setContextValue('subject', 'subject')
      ->setContextValue('message', 'hello');

    $params = [
      'subject' => 'subject',
      'message' => 'hello',
    ];

    $this->mailManager->mail(
      'rules', 'rules_action_mail_' . $this->action->getPluginId(),
      implode(', ', $to),
      LanguageInterface::LANGCODE_SITE_DEFAULT,
      $params,
      NULL
    )
      ->willReturn(['result' => TRUE])
      ->shouldBeCalledTimes(1);

    $this->logger->log(
      LogLevel::NOTICE,
      SafeMarkup::format('Successfully sent email to %to', ['%to' => implode(', ', $to)])
    )->shouldBeCalledTimes(1);

    $this->action->execute();
  }

}
