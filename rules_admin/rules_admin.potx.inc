<?php


/**
 * @file This is just a dummy file for the potx translation extractor.
 *   It contains additional calls to t(), so those strings are properly extracted.
 */

function rules_admin_potx() {
  // For the menu item
  t('Editing rule @label');

  // Calls to rules_handle_error_msg()
  t("%set can't be found. Probably the providing module has been deactivated.");
}