class Checkout
  params:
    ajax: ''
    assets: ''
    i18n:
      loading: 'Loading...'

  constructor: (@params) ->
    @_prepareStateField("#jigoshop_order_billing_state")
    @_prepareStateField("#jigoshop_order_shipping_state")

    jQuery('#jigoshop-login').on 'click', (event) ->
      event.preventDefault()
      jQuery('#jigoshop-login-form').slideToggle()
    jQuery('#create-account').on 'change', ->
      jQuery('#registration-form').slideToggle()
    jQuery('#different_shipping').on 'change', ->
      jQuery('#shipping-address').slideToggle()
      if (jQuery(this).is(':checked'))
        jQuery('#jigoshop_order_shipping_country').change()
      else
        jQuery('#jigoshop_order_billing_country').change()
    jQuery('#payment-methods').on 'change', 'li input[type=radio]', ->
      jQuery('#payment-methods li > div').slideUp()
      jQuery('div', jQuery(this).closest('li')).slideDown()
    jQuery('#shipping-calculator')
      .on 'click', 'input[type=radio]', @selectShipping
    jQuery('#jigoshop_order_billing_country').on 'change', (event) =>
      @updateCountry('billing', event)
    jQuery('#jigoshop_order_shipping_country').on 'change', (event) =>
      @updateCountry('shipping', event)
    jQuery('#jigoshop_order_billing_state').on 'change', @updateState.bind(@, 'billing')
    jQuery('#jigoshop_order_shipping_state').on 'change', @updateState.bind(@, 'shipping')
    jQuery('#jigoshop_order_billing_postcode').on 'change', @updatePostcode.bind(@, 'billing')
    jQuery('#jigoshop_order_shipping_postcode').on 'change', @updatePostcode.bind(@, 'shipping')

    # TODO: Copy shipping changing etc. here from Cart
    # TODO: Refactor Cart and Checkout (for sure) to create one place for many shared parameters and functions
  block: =>
    jQuery('#checkout > button').block
      message: '<img src="' + @params.assets + '/images/loading.gif" alt="' + @params.i18n.loading + '" />'
      css:
        padding: '20px'
        width: 'auto'
        height: 'auto'
        border: '1px solid #83AC31'
      overlayCss:
        opacity: 0.01

  unblock: ->
    jQuery('#checkout > button').unblock()

  _prepareStateField: (id) ->
    $field = jQuery(id)
    if !$field.is('select')
      return
    $replacement = jQuery(document.createElement('input'))
    .attr('type', 'text')
    .attr('id', $field.attr('id'))
    .attr('name', $field.attr('name'))
    .attr('class', $field.attr('class'))
    .val($field.val())
    data = []
    jQuery('option', $field).each ->
      data.push
        id: jQuery(this).val()
        text: jQuery(this).html()
    $field.replaceWith($replacement)
    $replacement.select2
      data: data

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
        addMessage('danger', result.error, 6000)

  updateCountry: (field, event) =>
    @block()
    jQuery('.noscript_state_field').remove()
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_checkout_change_country'
        field: field
        differentShipping: jQuery('#different_shipping').is(':checked')
        value: jQuery(event.target).val()
    )
    .done (result) =>
      if result.success? and result.success
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)
        stateClass = '#' + jQuery(event.target).attr('id').replace(/country/, 'state')

        if result.has_states
          data = []
          for own state, label of result.states
            data.push
              id: state
              text: label
          jQuery(stateClass).select2
            data: data
        else
          jQuery(stateClass).attr('type', 'text').select2('destroy').val('')
      else
        addMessage('danger', result.error, 6000)
      @unblock()

  updateState: (field) =>
    fieldClass = "#jigoshop_order_#{field}_state"
    @_updateShippingField('jigoshop_checkout_change_state', field, jQuery(fieldClass).val())

  updatePostcode: (field) =>
    fieldClass = "#jigoshop_order_#{field}_postcode"
    @_updateShippingField('jigoshop_checkout_change_postcode', field, jQuery(fieldClass).val())

  _updateShippingField: (action, field, value) =>
    @block()
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: action
        field: field
        differentShipping: jQuery('#different_shipping').is(':checked')
        value: value
    )
    .done (result) =>
      if result.success? and result.success
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)
      else
        addMessage('danger', result.error, 6000)
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
      else if html[shippingClass]?
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
