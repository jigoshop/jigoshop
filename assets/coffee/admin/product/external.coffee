class AdminProductExternal
  constructor: ->
    jQuery('#product-type').on 'change', @removeParameters
    jQuery('#product-variations > li').on 'change', '.variation-type', @removeVariationParameters

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'external'
      jQuery('.product_regular_price_field').slideDown()
      jQuery('.product-external').slideDown()
    else
      jQuery('.product-external').slideUp()

  removeVariationParameters: (event) ->
    $item = jQuery(event.target)
    $parent = $item.closest('li.variation')
    if $item.val() == 'external'
      jQuery('.product-external', $parent).slideDown()
    else
      jQuery('.product-external', $parent).slideUp()

jQuery ->
  new AdminProductExternal()
