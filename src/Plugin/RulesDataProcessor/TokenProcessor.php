<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesDataProcessor\TokenProcessor.
 */

namespace Drupal\rules\Plugin\RulesDataProcessor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Utility\Token;
use Drupal\rules\Context\DataProcessorInterface;
use Drupal\rules\Engine\RulesStateInterface;
use Drupal\rules\Exception\RulesEvaluationException;
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
   * The token service.
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
    // The Token API requires this metadata object, but it is useless for us
    // here so we just always pass the same instance and ignore it.
    $bubbleable_metdata = new BubbleableMetadata();
    // We only use the token service to scan for tokens in the text. The
    // replacements are done by using the data selector logic.
    foreach ($this->tokenService->scan($value) as $var_name => $tokens) {
      foreach ($tokens as $token) {
        // Remove the opening and closing bracket to form a data selector.
        $data_selector = substr($token, 1, -1);
        try {
          $replacement_data = $rules_state->applyDataSelector($data_selector);
          $replacements[$token] = $replacement_data->getString();
        }
        catch (RulesEvaluationException $exception) {
          // Data selector is invalid, so try to resolve the token with the
          // token service.
          if ($rules_state->hasVariable($var_name)) {
            $variable = $rules_state->getVariable($var_name);
            $token_type = $variable->getDataDefinition()->getDataType();
            // The Token system does not know about "enity:" data type prefixes,
            // so we have to remove them.
            $token_type = str_replace('entity:', '', $token_type);
            $data = [$token_type => $variable->getValue()];
            $replacements += $this->tokenService->generate($token_type, $tokens, $data, ['sanitize' => FALSE], $bubbleable_metdata);
          }
          else {
            $replacements += $this->tokenService->generate($var_name, $tokens, [], ['sanitize' => FALSE], $bubbleable_metdata);
          }
          // Remove tokens if no replacement value is found.
          $replacements += array_fill_keys($tokens, '');
        }
      }
    }

    // Apply the replacements now.
    $tokens = array_keys($replacements);
    $values = array_values($replacements);
    return str_replace($tokens, $values, $value);
  }

}
