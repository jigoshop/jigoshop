jQuery ($) ->
  $('div.free_shipping_countries_field').show()
  $('#free_shipping_available_for').on 'change', ->
    if $(this).val() == 'specific'
      $('#free_shipping_countries').closest('tr').show()
    else
      $('#free_shipping_countries').closest('tr').hide()
