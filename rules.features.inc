<?php
// $Id$

/**
 * @file
 * Provides Features integration for the Rules module, based upon the features
 * integration provided by the Entity API.
 */


/**
 * Controller handling the features integration.
 *
 * @todo: Add in dependencies and allow actions/conditions to pipe through
 *   further dependencies + use that to gather dependencies of invoked
 *   components.
 */
class RulesFeaturesController extends EntityDefaultFeaturesController {

  /**
   * Defines the result for hook_features_api().
   */
  public function api() {
    $info = parent::api();
    $info['rules_config']['default_file'] = FEATURES_DEFAULTS_CUSTOM;
    $info['rules_config']['default_filename'] = 'rules_defaults';
    return $info;
  }
}
