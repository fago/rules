<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesDataProcessor\TokenProcessor.
 */

namespace Drupal\rules\Plugin\RulesDataProcessor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Utility\Token;
use Drupal\rules\Context\DataProcessorInterface;
use Drupal\rules\Engine\RulesStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A data processor for token replacements.
 *
 * @RulesDataProcessor(
 *   id = "rules_tokens",
 *   label = @Translation("Token replacements")
 * )
 */
class TokenProcessor extends PluginBase implements DataProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The token service
   *
   * @var Token
   */
  protected $tokenService;

  /**
   * Constructs a TokenProcessor object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Utility\Token $token_service
   *   The token service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Token $token_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tokenService = $token_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($value, RulesStateInterface $rules_state) {
    $replacements = [];
    // We only use the token service to scan for tokens in the text. The
    // replacements are done by using the data selector logic.
    foreach ($this->tokenService->scan($value) as $tokens) {
      foreach ($tokens as $token) {
        // Remove the opening and closing bracket to form a data selector.
        $data_selector = substr($token, 1, -1);
        try {
          $replacement_data = $rules_state->applyDataSelector($data_selector);
          // @todo Data type specific formatting should happen here or we might
          //   invoke the Token API for that. Example: for a date we don't want
          //   to get the Unix timestamp in seconds but rather a formatted date
          //   string.
          $replacements[$token] = $replacement_data->getString();
        }
        catch (RulesEvaluationException $exception) {
          // Data selector is invalid, so ignore this token.
          // @todo We should probably invoke the token service here to check if
          //   there are other tokens that bypass the typed data system. But is
          //   that an actual use case?
        }
      }
    }

    // Apply the replacements now.
    $tokens = array_keys($replacements);
    $values = array_values($replacements);
    return str_replace($tokens, $values, $value);
  }

}
