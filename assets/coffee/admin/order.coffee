class AdminOrder
  params:
    ajax: ''
    tax_field: 'billing' # TODO: Properly load taxing field (billing or shipping)

  constructor: (@params) ->
    @newItemSelect()
    @_prepareStateField("#order_billing_state")
    @_prepareStateField("#order_shipping_state")

    jQuery('#add-item').on 'click', @newItemClick
    jQuery('.jigoshop-order table').on 'click', 'a.remove', @removeItemClick
    jQuery('.jigoshop-order table').on 'change', '.price input, .quantity input', @updateItem
    jQuery('.jigoshop-data')
      .on 'change', "#order_billing_country", @updateCountry
      .on 'change', "#order_shipping_country", @updateCountry
#      .on 'change', "#order_#{@params.tax_field}_state", @updateTaxState.bind(@, '#state')
#      .on 'change', '#noscript_state', @updateState.bind(@, '#noscript_state')
#      .on 'change', "#order_#{@params.tax_field}_postcode", @updateTaxPostcode
    jQuery('.jigoshop-totals')
      .on 'click', 'input[type=radio]', @selectShipping

  selectShipping: (e) =>
    $parent = jQuery(e.target).closest('div.jigoshop')

    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.change_shipping_method'
        order: $parent.data('order')
        method: jQuery(e.target).val()
    )
    .done (result) =>
      if result.success
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)
      else
        # TODO: It would be nice to have kind of helper for error messages
        alert result.error

  newItemSelect: =>
    jQuery('#new-item').select2
      minimumInputLength: 3
      ajax:
        url: @params.ajax
        type: 'post'
        dataType: 'json'
        data: (term) ->
          return {
          product: term
          action: 'jigoshop.admin.product.find'
          }
        results: (data) ->
          if data.success?
            return results: data.results
          return results: []

  newItemClick: (e) =>
    e.preventDefault()
    value = jQuery('#new-item').val()
    if value == ''
      return false

    $parent = jQuery(e.target).closest('table')

    # TODO: Check if already added - if so - increase quantity only
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.add_product'
        product: value
        order: $parent.data('order')
    .done (data) =>
      if data.success?
        jQuery(data.html.row).appendTo($parent)
        jQuery('#product-subtotal', $parent).html(data.html.product_subtotal)
        @_updateTotals(data.html.total, data.html.subtotal)
        @_updateTaxes(data.tax, data.html.tax)

  updateItem: (e) =>
    e.preventDefault()
    $row = jQuery(e.target).closest('tr')
    $parent = $row.closest('table')

    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.update_product'
        product: $row.data('id')
        order: $parent.data('order')
        price: jQuery('.price input', $row).val()
        quantity: jQuery('.quantity input', $row).val()
    .done (data) =>
      if data.success?
        jQuery('.total p', $row).html(data.html.item_cost)
        jQuery('#product-subtotal', $parent).html(data.html.product_subtotal)
        @_updateTotals(data.html.total, data.html.subtotal)
        @_updateTaxes(data.tax, data.html.tax)

  removeItemClick: (e) =>
    e.preventDefault()
    $row = jQuery(e.target).closest('tr')
    $parent = $row.closest('table')

    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.remove_product'
        product: $row.data('id')
        order: $parent.data('order')
    .done (data) =>
      if data.success?
        $row.remove()
        jQuery('#product-subtotal', $parent).html(data.html.product_subtotal)
        @_updateTaxes(data.tax, data.html.tax)
        @_updateTotals(data.html.total, data.html.subtotal)

  updateCountry: (e) =>
    $target = jQuery(e.target)

    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.change_country'
        value: $target.val()
        order: $target.closest('.jigoshop').data('order')
    )
    .done (result) =>
      if result.success
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateShipping(result.shipping, result.html.shipping)

        fieldClass = "#order_#{@params.tax_field}_country"
        if $target.attr('id') == fieldClass
          @_updateTaxes(result.tax, result.html.tax)

        $field = jQuery('#' + $target.attr('id').replace(/_country/, '_state'))

        if result.has_states
          data = []
          for own state, label of result.states
            data.push
              id: state
              text: label
          $field.select2
            data: data
        else
          $field.select2('destroy').val('')

  _updateTaxes: (taxes, html) ->
    for own taxClass, tax of html
      fieldClass = ".order_tax_#{taxClass}_field"
      $tax = jQuery(fieldClass)
      jQuery("label", $tax).html(tax.label)
      jQuery("p", $tax).html(tax.value).show()
      if taxes[taxClass] > 0
        $tax.show()
      else
        $tax.hide()

  _updateTotals: (total, subtotal) ->
    jQuery('#subtotal').html(subtotal)
    jQuery('#total').html(total)

  _updateShipping: (shipping, html) ->
    for own shippingClass, value of html
      $method = jQuery("#shipping-#{shippingClass}")
      jQuery('span', $method).html(value)
      if shipping[shippingClass] != -1
        $method.show()
      else
        $method.hide()

  _prepareStateField: (id) ->
    $field = jQuery(id)
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

#  updateState: (field) =>
#    @_updateShippingField('jigoshop_cart_change_state', jQuery(field).val())
#
#  updatePostcode: =>
#    @_updateShippingField('jigoshop_cart_change_postcode', jQuery('#postcode').val())
#
#  _updateShippingField: (action, value) =>
#    jQuery.ajax(@params.ajax,
#      type: 'post'
#      dataType: 'json'
#      data:
#        action: action
#        value: value
#    )
#    .done (result) =>
#      if result.success == true
#        jQuery('#shipping-calculator th p > span').html(result.html.estimation)
#        @_updateTotals(result.html.total, result.html.subtotal)
#        @_updateTaxes(result.tax, result.html.tax)
#        @_updateShipping(result.shipping, result.html.shipping)

jQuery ->
  new AdminOrder(jigoshop_admin_order)
