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
  delay 3000,  -> $('.alert-success').slideUp ->
    $(this).remove()
  delay 4000,  -> $('.alert-warning').slideUp ->
    $(this).remove()
  delay 8000,  -> $('.alert-error').slideUp ->
    $(this).remove()
