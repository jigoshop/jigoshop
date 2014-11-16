class AdminProductSimple
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#product-type').on 'change', @removeParameters

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'simple'
      jQuery('.product_regular_price_field').slideDown()

jQuery ->
  new AdminProductSimple(jigoshop_admin_product_simple)
