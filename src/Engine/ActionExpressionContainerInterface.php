<?php

namespace Drupal\rules\Engine;

use Drupal\rules\Context\ContextConfig;

/**
 * Contains action expressions.
 */
interface ActionExpressionContainerInterface extends ActionExpressionInterface, ExpressionContainerInterface {

  /**
   * Creates an action expression and adds it to the container.
   *
   * @param string $action_id
   *   The action plugin id.
   * @param \Drupal\rules\Context\ContextConfig $config
   *   (optional) The configuration for the specified plugin.
   *
   * @return $this
   */
  public function addAction($action_id, ContextConfig $config = NULL);

}
