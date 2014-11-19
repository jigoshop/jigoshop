jQuery ($) ->
  $('#restrict_selling_locations').on 'change', -> $('#selling_locations').closest('tr').toggle()
  $('#selling_locations').show().closest('div.form-group').show()
