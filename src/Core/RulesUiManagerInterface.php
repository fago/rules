<?php

/**
 * @file
 * Contains Drupal\rules\Core\RulesUiManagerInterface.
 */

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Interface for the 'rules_ui' plugin manager.
 *
 * RulesUI plugins allow the definition of multiple RulesUIs instances, possibly
 * being included in some other UI.
 */
interface RulesUiManagerInterface extends PluginManagerInterface {

}
