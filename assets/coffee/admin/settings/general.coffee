jQuery ($) ->
  $('#show_message').on 'change', -> $('#custom_message').closest('tr').toggle()
  $('#custom_message').show().closest('div.form-group').show()
