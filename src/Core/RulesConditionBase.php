<?php

namespace Drupal\rules\Core;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\rules\Context\ContextProviderTrait;

/**
 * Base class for rules conditions.
 *
 * @todo Figure out whether buildConfigurationForm() is useful to Rules somehow.
 */
abstract class RulesConditionBase extends ConditionPluginBase implements RulesConditionInterface {

  use ContextProviderTrait;
  use ExecutablePluginTrait;
  use ConfigurationAccessControlTrait;

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions(array $selected_data) {
    // Do not refine anything by default.
  }

  /**
   * {@inheritdoc}
   */
  public function assertMetadata(array $selected_data) {
    // Nothing to assert by default.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getContextValue($name) {
    try {
      return parent::getContextValue($name);
    }
    catch (ContextException $e) {
      // Catch the undocumented exception thrown when no context value is set
      // for a required context.
      // @todo: Remove once https://www.drupal.org/node/2677162 is fixed.
      if (strpos($e->getMessage(), 'context is required') === FALSE) {
        throw $e;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // Provide a reasonable default implementation that calls doEvaluate() while
    // passing the defined context as arguments.
    $args = [];
    foreach ($this->getContextDefinitions() as $name => $definition) {
      $args[$name] = $this->getContextValue($name);
    }
    return call_user_func_array([$this, 'doEvaluate'], $args);
  }

}
