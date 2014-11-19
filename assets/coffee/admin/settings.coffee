jQuery ($) ->
  $('tr > td .hidden').closest('tr').hide()
  $('#show_message').on 'change', -> $('#custom_message').closest('tr').toggle()
  $('#custom_message').show().closest('div.form-group').show()

  delay 3000,  -> $('.settings-error.updated').slideUp ->
    $(this).remove()
  delay 3000,  -> $('.alert-success').slideUp ->
    $(this).remove()
  delay 4000,  -> $('.alert-warning').slideUp ->
    $(this).remove()
  delay 8000,  -> $('.alert-error').slideUp ->
    $(this).remove()
