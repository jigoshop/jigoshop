jQuery ($) ->
  $('tr > td .hidden').closest('tr').hide()
  $('#show_message').on 'change', ->
    if $(this).is(':checked')
      $('#custom_message').removeClass('hidden').closest('tr').show()
    else
      $('#custom_message').addClass('hidden').closest('tr').hide()
  .change()
  delay 3000,  -> $('.settings-error.updated').slideUp ->
    $(this).remove()
