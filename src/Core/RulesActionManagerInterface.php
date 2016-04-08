<?php

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\CategorizingPluginManagerInterface;
use Drupal\Core\Plugin\Context\ContextAwarePluginManagerInterface;

/**
 * Interface the Rules Action plugin manager of the Rules actions API.
 *
 * @see \Drupal\Core\Annotation\Action
 * @see \Drupal\Core\Action\ActionInterface
 * @see \Drupal\Core\Action\ActionBase
 * @see plugin_api
 */
interface RulesActionManagerInterface extends CategorizingPluginManagerInterface, ContextAwarePluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\rules\Core\RulesActionInterface
   *   A fully configured plugin instance.
   */
  public function createInstance($plugin_id, array $configuration = []);

}
