Drupal.behaviors.RulesAdminSetAddArg = function (context) {
  $('.rules-argument-data-type:not(.RulesAdminSetAddArg-processed)', context).addClass('RulesAdminSetAddArg-processed').each(function () {
    $('select', this).bind("change", function() {
    
      if ($(this).parents("td").next(".rules-argument-name").find('input').val() == '')
        $(this).parents("td").next(".rules-argument-name").find('input').val( $(this).val() );

      if ($(this).parents("td").prev(".rules-argument-label").find('input').val() == '')
        $(this).parents("td").prev(".rules-argument-label").find('input').val(
             $("option:selected", this).text()
         );

    });
  });
};
