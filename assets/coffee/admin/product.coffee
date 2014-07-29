jQuery ($) ->
  $('.jigoshop_product_data a').click (e) ->
    e.preventDefault()
    $(this).tab('show')
