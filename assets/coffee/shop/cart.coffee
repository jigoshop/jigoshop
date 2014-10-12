class Cart
  params:
    ajax: ''
    assets: ''
    i18n:
      loading: 'Loading...'

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
    jQuery('#country').change() # TODO: Get rid of this call (this will also fix non-JS cart functioning)

  block: =>
    jQuery('#content').block
      message: '<img src="' + @params.assets + '/images/loading.gif" alt="' + @params.i18n.loading + '" />'
      css:
        padding: '20px'
        width: 'auto'
        height: 'auto'
        border: '1px solid #83AC31'
      overlayCss:
        opacity: 0.01

  unblock: ->
    jQuery('#content').unblock()

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
      if result.success
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)
      else
        # TODO: It would be nice to have kind of helper for error messages
        alert result.error

  updateCountry: =>
    @block()
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
        else
          jQuery('#state').attr('type', 'text').select2('destroy').val('')
      @unblock()

  updateState: =>
    @_updateShippingField('jigoshop_cart_change_state', jQuery('#state').val())

  updatePostcode: =>
    @_updateShippingField('jigoshop_cart_change_postcode', jQuery('#postcode').val())

  _updateShippingField: (action, value) =>
    @block()
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
      @unblock()

  removeItem: (e) =>
    # TODO: Ask nicely if client is sure?
    e.preventDefault()
    $item = jQuery(e.target).closest('tr')
    jQuery('.product-quantity', $item).val(0)
    @updateQuantity(e)

  updateQuantity: (e) =>
    $item = jQuery(e.target).closest('tr')
    @block()
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
      @unblock()

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
