class AdminProductExternal
  constructor: ->
    jQuery('#product-type').on 'change', @removeParameters

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'external'
      jQuery('.product_regular_price_field').slideDown()
      jQuery('.product-external').slideDown()
    else
      jQuery('.product-external').slideUp()

jQuery ->
  new AdminProductExternal()
