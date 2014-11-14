class Checkout
  params:
    ajax: ''
    assets: ''
    i18n:
      loading: 'Loading...'

  constructor: (@params) ->
    jQuery('#different_shipping').on 'change', -> jQuery('#shipping-address').slideToggle()
    jQuery('#payment-methods').on 'change', 'li input[type=radio]', ->
      jQuery('#payment-methods li > div').slideUp()
      jQuery('div', jQuery(this).closest('li')).slideDown()
    jQuery('#shipping-calculator')
      .on 'click', 'input[type=radio]', @selectShipping

    # TODO: Copy shipping changing etc. here from Cart
    # TODO: Refactor Cart and Checkout (for sure) to create one place for many shared parameters and functions

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
    jQuery('.noscript_state_field').remove()
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

  updateState: (field) =>
    @_updateShippingField('jigoshop_cart_change_state', jQuery(field).val())

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

  _updateTotals: (total, subtotal) ->
    jQuery('#cart-total > td > strong').html(total)
    jQuery('#cart-subtotal > td').html(subtotal)

  _updateShipping: (shipping, html) ->
    for own shippingClass, value of shipping
      $method = jQuery("#shipping-#{shippingClass}")
      if $method.length > 0
        if value > -1
          jQuery('span', $method).html(html[shippingClass].price)
        else
          $method.slideUp -> jQuery(this).remove()
      else
        $item = jQuery(html[shippingClass].html)
        $item.hide().appendTo(jQuery('#shipping-methods')).slideDown()

  _updateTaxes: (taxes, html) ->
    for own taxClass, tax of html
      $tax = jQuery("#tax-#{taxClass}")
      jQuery("th", $tax).html(tax.label)
      jQuery("td", $tax).html(tax.value)
      if taxes[taxClass] > 0
        $tax.show()
      else
        $tax.hide()

jQuery ->
  new Checkout(jigoshop_checkout)
