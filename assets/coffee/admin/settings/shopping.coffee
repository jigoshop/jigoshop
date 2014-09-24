jQuery ($) ->
  $('#restrict_selling_locations').on 'change', ->
    if $(this).is(':checked')
      $('#selling_locations').removeClass('hidden').closest('tr').show()
      $('.selling_locations_field').removeClass('hidden')
    else
      $('#selling_locations').addClass('hidden').closest('tr').hide()
  .change()
