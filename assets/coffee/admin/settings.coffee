jQuery ($) ->
  $('tr > td .hidden').closest('tr').hide()
  $('#show_message').on 'change', ->
    if $(this).is(':checked')
      $('#custom_message').closest('tr').show()
    else
      $('#custom_message').closest('tr').hide()
  .change()
  delay 3000,  -> $('.settings-error.updated').slideUp ->
    $(this).remove()