// $Id$

// Registers the rules namespace.
Drupal.rules = Drupal.rules || {};

(function($) {
  Drupal.behaviors.rules_autocomplete = {
    attach: function(context) {
      var autocomplete_settings = Drupal.settings.rules_autocomplete;

      $('input.rules-autocomplete').once(function() {
        var input = this;
        new Drupal.rules.autocomplete(input, autocomplete_settings[$(input).attr('id')]);
      });
    }
  };

  /**
   * Rules autocomplete object.
   */
  Drupal.rules.autocomplete = function(input, settings) {
    this.id = settings.inputId;
    this.uri = location.protocol + '//' + location.host + Drupal.settings.basePath + settings.source;
    this.jqObject = $('#' + this.id);
    this.cache = new Array();
    this.jqObject.addClass('ui-corner-left');

    this.selected = false;
    this.groupSelected = false;
    this.opendByFocus = false;

    this.button = $('<span>&nbsp;</span>');
    this.button.attr( {
      'tabIndex': -1,
      'title': 'Show all items'
    });
    this.button.insertAfter(this.jqObject);

    this.button.button( {
      icons: {
        primary: 'ui-icon-triangle-1-s'
      },
      text: false
    });

    // Don't round the left corners.
    this.button.removeClass('ui-corner-all');
    this.button.addClass('ui-corner-right ui-button-icon rules-autocomplete-button');

    this.jqObject.autocomplete();
    this.jqObject.autocomplete("option", "minLength", 0);
    // Add a custom class, so we can style the autocomplete box without
    // interfering with other jquery autocomplete widgets.
    this.jqObject.autocomplete("widget").addClass('rules-autocomplete');

    // Save the current rules_autocomplete object, so it can be used in
    // handlers.
    var instance = this;

    // Event handlers
    this.jqObject.focus(function() {
      // If the something was selected, the window should not open again.
      if (!instance.selected || instance.groupSelected) {
        instance.open();
        instance.opendByFocus = true;
      }
      else {
        // If the something was selected, the window should not open again.
        instance.selected = false;
      }
    });

    // Needed when the window is closed but the textfield has the focus.
    this.jqObject.click(function() {
      if (!instance.opendByFocus) {
        instance.toggle();
      }
      else {
        instance.close();
      }
    });

    this.jqObject.bind("autocompleteselect", function(event, ui) {
      instance.close();
      instance.selected = true;
    });

    this.jqObject.autocomplete("option", "source", function(request, response) {
      if (request.term in instance.cache) {
        response(instance.cache[request.term]);
        return;
      }
      $.ajax( {
        url: instance.uri + '/' + request.term,
        dataType: "json",
        success: function(data) {
          instance.success(data, request, response);
        }
      });
    });

    // Since jquery autocomplete by default strips html text by using .text()
    // we need our own _renderItem function to display html content.
    this.jqObject.data("autocomplete")._renderItem = function(ul, item) {
      return $("<li></li>").data("item.autocomplete", item).append("<a>" + item.label + "</a>").appendTo(ul);
    };

    this.button.click(function() {
      instance.toggle();
    });
  };

  /**
   * Success function for Rules autocomplete object.
   */
  Drupal.rules.autocomplete.prototype.success = function(data, request, response) {
    var list = new Array();
    jQuery.each(data, function(index, value) {
      list.push( {
        label: value,
        value: index
      });
    });
    this.cache[request.term] = list;
    response(list);
  };

  /**
   * Open the autocomplete window.
   */
  Drupal.rules.autocomplete.prototype.open = function() {
    this.jqObject.autocomplete("search", this.jqObject.val());
    this.button.addClass("ui-state-focus");
    this.groupSelected = false;
  };

  /**
   * Close the autocomplete window.
   */
  Drupal.rules.autocomplete.prototype.close = function() {
    var value = this.jqObject.val();
    // If the Selector is group, then keep the selection list open.
    if (value.substring(value.length - 1, value.length) != ':') {
      this.jqObject.autocomplete("close");
      this.button.removeClass("ui-state-focus");
      this.opendByFocus = false;
    }
    else {
      this.groupSelected = true;
    }
  };

  /**
   * Toogle the autcomplete window.
   */
  Drupal.rules.autocomplete.prototype.toggle = function() {
    if (this.jqObject.autocomplete("widget").is(":visible")) {
      this.close();
    }
    else {
      this.open();
    }
  };

})(jQuery);
