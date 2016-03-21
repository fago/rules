/**
 * @file
 * Forked from core's autocomplete.
 */

(function ($, Drupal) {

  'use strict';

  var autocomplete;

  /**
   * JQuery UI autocomplete source callback.
   *
   * @param {object} request
   *   The request object.
   * @param {function} response
   *   The function to call with the response.
   */
  function sourceData(request, response) {
    var elementId = this.element.attr('id');

    if (!(elementId in autocomplete.cache)) {
      autocomplete.cache[elementId] = {};
    }

    /**
     * Transforms the data object into an array and update autocomplete results.
     *
     * @param {object} data
     *   The data sent back from the server.
     */
    function sourceCallbackHandler(data) {
      autocomplete.cache[elementId][term] = data;

      response(data);
    }

    // Get the desired term and construct the autocomplete URL for it.
    var term = request.term;

    // Check if the term is already cached.
    if (autocomplete.cache[elementId].hasOwnProperty(term)) {
      response(autocomplete.cache[elementId][term]);
    }
    else {
      var options = $.extend({success: sourceCallbackHandler, data: {q: term}}, autocomplete.ajax);
      $.ajax(this.element.attr('data-autocomplete-path'), options);
    }
  }

  /**
   * Handles an autocompletefocus event.
   *
   * @return {bool}
   *   Always returns false.
   */
  function focusHandler() {
    return false;
  }

  /**
   * Handles the autocomplete selection event.
   *
   * Restarts autocompleting when the selection ends in a dot, for nested data
   * selectors.
   *
   * @param {object} event
   *   The event object.
   * @param {object} ui
   *   The UI object holding the selected value.
   */
  function selectHandler(event, ui) {
    var input_value = ui.item.value;
    if (input_value.substr(input_value.length - 1) === '.') {
      $(event.target).trigger('keydown');
    }
  }

  /**
   * Override jQuery UI _renderItem function to output HTML by default.
   *
   * @param {jQuery} ul
   *   jQuery collection of the ul element.
   * @param {object} item
   *   The list item to append.
   *
   * @return {jQuery}
   *   jQuery collection of the ul element.
   */
  function renderItem(ul, item) {
    return $('<li>')
      .append($('<a>').html(item.label))
      .appendTo(ul);
  }

  /**
   * Attaches the autocomplete behavior to all required fields.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the autocomplete behaviors.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches the autocomplete behaviors.
   */
  Drupal.behaviors.rules_autocomplete = {
    attach: function (context) {
      // Act on textfields with the "rules-autocomplete" class.
      var $autocomplete = $(context).find('input.rules-autocomplete').once('autocomplete');
      if ($autocomplete.length) {

        var closing = false;

        $.extend(autocomplete.options, {
          close: function() {
            // Avoid double-pop-up issue.
            closing = true;
            setTimeout(function() {
              closing = false;
            }, 300);
          }
        });
        // Use jQuery UI Autocomplete on the textfield.
        $autocomplete.autocomplete(autocomplete.options)
          .each(function() {
            $(this).data('ui-autocomplete')._renderItem = autocomplete.options.renderItem;
            // Immediately pop out the autocomplete when the field gets focus.
            $(this).focus(function() {
              if (!closing) {
                $(this).autocomplete('search');
              }
            });
          });
      }
    },
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        $(context).find('input.rules-autocomplete')
          .removeOnce('autocomplete')
          .autocomplete('destroy');
      }
    }
  };

  /**
   * Autocomplete object implementation.
   *
   * @namespace Drupal.autocomplete
   */
  autocomplete = {
    cache: {},

    /**
     * JQuery UI option object.
     *
     * @name Drupal.autocomplete.options
     */
    options: {
      source: sourceData,
      focus: focusHandler,
      select: selectHandler,
      renderItem: renderItem,
      minLength: 0
    },
    ajax: {
      dataType: 'json'
    }
  };

})(jQuery, Drupal);
