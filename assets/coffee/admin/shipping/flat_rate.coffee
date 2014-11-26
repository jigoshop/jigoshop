jQuery ($) ->
  $('div.flat_rate_countries_field').show()
  $('#flat_rate_available_for').on 'change', ->
    if $(this).val() == 'specific'
      $('#flat_rate_countries').closest('tr').show()
    else
      $('#flat_rate_countries').closest('tr').hide()
