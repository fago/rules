// $Id$

(function ($) {
Drupal.behaviors.rulesElementsParametersSwitch = {
  attach: function (context) {
    $('input[id*=switch]').each(function () {
      $(this).remove()
    })
    $('.rules_parameter_switch').each(function () {
      $(this).removeClass('rules_hidden');
    })
  }
};
})(jQuery);