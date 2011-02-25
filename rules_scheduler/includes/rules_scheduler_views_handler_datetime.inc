<?php

/**
 * @file
 * An extended subclass for datetime handling.
 * We need this to correctly convert our SQL datetime field to local user date/time.
 */
class rules_scheduler_views_handler_datetime extends views_handler_field_date {

  function render($values) {
    $value = $values->{$this->field_alias};
    // overwrite date string with timestamp
    $values->{$this->field_alias} = rules_gmstrtotime($value);
    // call parent method with modified values
    return parent::render($values);
  }
}
