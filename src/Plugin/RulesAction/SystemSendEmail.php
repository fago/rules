<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides "Send email" rules action.
 *
 * @RulesAction(
 *   id = "rules_send_email",
 *   label = @Translation("Send email"),
 *   category = @Translation("System"),
 *   context = {
 *     "to" = @ContextDefinition("email",
 *       label = @Translation("Send to"),
 *       description = @Translation("Email address(es) drupal will send an email to."),
 *       multiple = TRUE,
 *     ),
 *     "subject" = @ContextDefinition("string",
 *       label = @Translation("Subject"),
 *       description = @Translation("The email's subject."),
 *     ),
 *     "message" = @ContextDefinition("string",
 *       label = @Translation("Message"),
 *       description = @Translation("The email's message body."),
 *     ),
 *     "reply" = @ContextDefinition("email",
 *       label = @Translation("Reply to"),
 *       description = @Translation("The mail's reply-to address. Leave it empty to use the site-wide configured address."),
 *       default_value = NULL,
 *       required = FALSE,
 *     ),
 *     "language" = @ContextDefinition("language",
 *       label = @Translation("Language"),
 *       description = @Translation("If specified, the language used for getting the mail message and subject."),
 *       default_value = NULL,
 *       required = FALSE,
 *     ),
 *   }
 * )
 *
 * @todo: Define that message Context should be textarea comparing with textfield Subject
 * @todo: Add access callback information from Drupal 7.
 */
class SystemSendEmail extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The logger channel the action will write log messages to.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs a SendEmail object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Psr\Log\LoggerInterface $logger
   *   The alias storage service.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger, MailManagerInterface $mail_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('rules'),
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * Send a system email.
   *
   * @param string[] $to
   *   Email addresses of the recipients.
   * @param string $subject
   *   Subject of the email.
   * @param string $message
   *   Email message text.
   * @param string|null $reply
   *   (optional) Reply to email address.
   * @param \Drupal\Core\Language\LanguageInterface|null $language
   *   (optional) Language code.
   */
  protected function doExecute(array $to, $subject, $message, $reply = NULL, LanguageInterface $language = NULL) {
    $langcode = isset($language) ? $language->getId() : LanguageInterface::LANGCODE_SITE_DEFAULT;
    $params = [
      'subject' => $subject,
      'message' => $message,
    ];
    // Set a unique key for this mail.
    $key = 'rules_action_mail_' . $this->getPluginId();

    $recipients = implode(', ', $to);
    $message = $this->mailManager->mail('rules', $key, $recipients, $langcode, $params, $reply);
    if ($message['result']) {
      $this->logger->notice('Successfully sent email to %recipient', ['%recipient' => $recipients]);
    }

  }

}
