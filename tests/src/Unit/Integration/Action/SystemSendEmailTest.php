<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\SystemSendEmail
 * @group RulesAction
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
    $logger_factory = $this->prophesize(LoggerChannelFactoryInterface::class);
    $logger_factory->get('rules')->willReturn($this->logger->reveal());

    $this->mailManager = $this->prophesize(MailManagerInterface::class);

    // @todo this is wrong, the logger is no factory.
    $this->container->set('logger.factory', $logger_factory->reveal());
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

    $this->logger->notice(
      // @todo assert the actual message here, but PHPunit goes into an endless
      // loop with that.
      Argument::any(), Argument::any()
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

    $this->logger->notice(
      // @todo assert the actual message here, but PHPunit goes into an endless
      // with that.
      Argument::any(), Argument::any()
    )->shouldBeCalledTimes(1);

    $this->action->execute();
  }

}
