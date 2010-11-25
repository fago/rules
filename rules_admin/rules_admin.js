Drupal.behaviors.RulesAdminSetAddArg = function (context) {
  $('.rules-argument-data-type:not(.RulesAdminSetAddArg-processed)', context).addClass('RulesAdminSetAddArg-processed').each(function () {
    $('select', this).bind("change", function() {
    
      if ($(this).parents("td").nextAll(".rules-argument-name").find('input').val() == '')
        $(this).parents("td").nextAll(".rules-argument-name").find('input').val( $(this).val() );

      if ($(this).parents("td").nextAll(".rules-argument-label").find('input').val() == '')
        $(this).parents("td").nextAll(".rules-argument-label").find('input').val(
             $("option:selected", this).text()
         );

    });
  });
};

Drupal.behaviors.RulesAdminMachineName = function (context) {
  // Add rule form machine-readable JS
  $('#edit-label').addClass('processed').after(' <small class="rules-name-suffix">&nbsp;</small>');
  $('#edit-name').parents('.form-item').hide();

  // Add a click function to our rules name suffix.
  $('.rules-name-suffix').click(function() {
        $('#edit-name').parents('.form-item').show();
        $('.rules-name-suffix').hide();
        $('#edit-label').unbind('keyup');
        return false;
  });

  $('#edit-label').keyup(function() {
    var machine = $(this).val().toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^[^a-z]/, 'a');
    if (machine !== '') {
      $('#edit-name').val(machine);
      $('.rules-name-suffix').html(' ' + Drupal.t('Machine name:') + ' ' + machine + ' [').append($('<a href="#">'+ Drupal.t('Edit') +'</a>')).append(']');
    }
    else {
      $('#edit-name').val(machine);
      $('.rules-name-suffix').text('');
    }
  });
  // If we already have a machine name filled in, then just use that.
  if($('#edit-name').val() !== '') {
    $('.rules-name-suffix').html(' ' + Drupal.t('Machine name:') + ' ' + $('#edit-name').val() + ' [').append($('<a href="#">'+ Drupal.t('Edit') +'</a>')).append(']');
  }
  // If there's nothing in the field, then we can just trigger the event.
  else {
    $('#edit-label').keyup();
  }
}
