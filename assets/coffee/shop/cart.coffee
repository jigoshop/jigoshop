class Cart
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#cart')
      .on 'change', '.product-quantity input', @updateQuantity
      .on 'click', '.product-remove a', @removeItem
    jQuery('#shipping-calculator')
      .on 'click', '#change-destination', @changeDestination
      .on 'click', '.close', @changeDestination
      .on 'click', 'input[type=radio]', @selectShipping
      .on 'change', '#country', @updateCountry
      .on 'change', '#state', @updateState
      .on 'change', '#postcode', @updatePostcode
    # TODO: Maybe blocking while doing update?
    jQuery('#country').change()

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
    .done (result) =>
      @_updateTotals(result.html.total, result.html.subtotal)
      @_updateTaxes(result.tax, result.html.tax)

  updateCountry: =>
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_change_country'
        value: jQuery('#country').val()
    )
    .done (result) =>
      if result.success == true
        jQuery('#shipping-calculator th p > span').html(result.html.estimation)
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)

        if result.has_states
          data = []
          for own state, label of result.states
            data.push
              id: state
              text: label
          jQuery('#state').select2
            data: data
            initSelection: (element, callback) ->
              value = element.val()
              if value != ''
                callback(
                  id: value
                  text: result.states[value]
                )
        else
          jQuery('#state').select2('destroy').val('')

  updateState: =>
    @_updateShippingField('jigoshop_cart_change_state', jQuery('#state').val())

  updatePostcode: =>
    @_updateShippingField('jigoshop_cart_change_postcode', jQuery('#postcode').val())

  _updateShippingField: (action, value) =>
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: action
        value: value
    )
    .done (result) =>
      if result.success == true
        jQuery('#shipping-calculator th p > span').html(result.html.estimation)
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)

  removeItem: (e) =>
    # TODO: Ask nicely if client is sure?
    e.preventDefault()
    $item = jQuery(e.target).closest('tr')
    jQuery('.product-quantity', $item).val(0)
    @updateQuantity(e)

  updateQuantity: (e) =>
    $item = jQuery(e.target).closest('tr')
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_update_item'
        item: $item.data('id')
        quantity: jQuery(e.target).val()
    )
    .done (result) =>
      if result.success == true
        if result.empty_cart? == true
          $empty = jQuery(result.html).hide()
          $cart = jQuery('#cart')
          $cart.after($empty)
          $cart.slideUp()
          $empty.slideDown()
          return

        if result.remove_item? == true
          $item.remove()
        else
          jQuery('.product-subtotal', $item).html(result.html.item_subtotal)

        jQuery('#product-subtotal > td').html(result.html.product_subtotal)
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)

  _updateTotals: (total, subtotal) ->
    jQuery('#cart-total > td').html(total)
    jQuery('#cart-subtotal > td').html(subtotal)

  _updateShipping: (shipping, html) ->
    for own shippingClass, value of html
      $method = jQuery("#shipping-#{shippingClass}")
      jQuery('span', $method).html(value)
      if shipping[shippingClass] != -1
        $method.show()
      else
        $method.hide()

  _updateTaxes: (taxes, html) ->
    for own taxClass, tax of html
      $tax = jQuery("#tax-#{taxClass}")
      jQuery("th", $tax).html(tax.label)
      jQuery("td", $tax).html(tax.value)
      if taxes[taxClass] > 0
        $tax.show()
      else
        $tax.hide()

jQuery () ->
  new Cart(jigoshop)
