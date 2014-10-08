class Cart
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#cart').on 'change', '.product-quantity input', @updateQuantity
    jQuery('#shipping-calculator')
      .on 'click', '#change-destination', @changeDestination
      .on 'click', '.close', @changeDestination
      .on 'click', 'input[type=radio]', @selectShipping
    # TODO: Add functions for changing destination
    # TODO: Maybe blocking while doing update?

  changeDestination: (e) ->
    e.preventDefault()
    jQuery('#shipping-calculator td > div').slideToggle()
    jQuery('#change-destination').slideToggle()
    return false

  selectShipping: =>
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_select_shipping'
        method: jQuery('#shipping-calculator input[type=radio]:checked').val()
    )
    .done (result) ->
      jQuery('#cart-total > td').html(result.html.total)
      jQuery('#cart-subtotal > td').html(result.html.subtotal)
      for own taxClass, tax of result.html.tax
        jQuery("#tax-#{taxClass} > td").html(tax)

  updateQuantity: ->
    $item = jQuery(this).closest('tr')
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_update_item'
        item: jQuery(this).closest('tr').data('id')
        quantity: jQuery(this).val()
    )
    .done (result) ->
      if result.success == true
        jQuery('.product-subtotal', $item).html(result.html.item_subtotal)
      else
        $item.remove()
      jQuery('#cart-total > td').html(result.html.total)
      jQuery('#cart-subtotal > td').html(result.html.subtotal)
      for own taxClass, tax of result.html.tax
        jQuery("#tax-#{taxClass} > td").html(tax)

jQuery () ->
  new Cart(jigoshop)
