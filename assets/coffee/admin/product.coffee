jQuery ($) ->
  $('.jigoshop_product_data a').on 'click', (e) ->
    e.preventDefault()
    $(this).tab('show')
  $('#stock-manage').on 'change', ->
    if $(this).is(':checked')
      $('.stock-status_field').slideUp()
      $('.stock-status').slideDown()
    else
      $('.stock-status_field').slideDown()
      $('.stock-status').slideUp()
  $('#sales-enabled').on 'change', ->
    if $(this).is(':checked')
      $('.schedule').slideDown()
    else
      $('.schedule').slideUp()
