<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides "Page redirect" rules action.
 *
 * @RulesAction(
 *   id = "rules_page_redirect",
 *   label = @Translation("Page redirect"),
 *   category = @Translation("System"),
 *   context = {
 *     "url" = @ContextDefinition("string",
 *       label = @Translation("URL"),
 *       description = @Translation("A Drupal path, path alias, or external URL to redirect to. Enter (optional) queries after ? and (optional) anchor after #."),
 *     ),
 *   }
 * )
 */
class SystemPageRedirect extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The logger for the rules channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * The current request.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a PageRedirect object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path_stack
   *   The current path stack service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, CurrentPathStack $current_path_stack, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('rules');
    $this->currentPathStack = $current_path_stack;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('path.current'),
      $container->get('request_stack')
    );
  }

  /**
   * Redirect to a provided url at the end of the request.
   *
   * @param string $url
   *   Redirect destination url.
   */
  protected function doExecute($url) {
    $current_path = $this->currentPathStack->getPath();
    $is_rules_admin_page = strpos($current_path, 'admin/config/workflow/rules') !== FALSE;

    // Make sure we do not redirect away from the rules admin pages.
    if ($is_rules_admin_page) {
      $this->logger->warning('Skipped page redirect on a rules admin page.');
      return;
    }

    // Make sure we do not redirect during batch processing.
    $batch = batch_get();
    if (isset($batch['current_set'])) {
      $this->logger->warning('Skipped page redirect during batch processing.');
      return;
    }

    $this->request->attributes->set('_rules_redirect_action_url', $url);
  }

}
