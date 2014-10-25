class AdminOrder
  params:
    ajax: ''
    tax_field: 'billing' # TODO: Properly load taxing field (billing or shipping)

  constructor: (@params) ->
    @newItemSelect()
    jQuery('#add-item').on 'click', @newItemClick
    jQuery('.jigoshop-order table').on 'click', 'a.remove', @removeItemClick
    jQuery('.jigoshop-order table').on 'change', '.price input, .quantity input', @updateItem
    jQuery('.jigoshop-data')
      .on 'change', "#order_#{@params.tax_field}_country", @updateTaxCountry
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
        jQuery('#subtotal').html(data.html.subtotal)
        jQuery('#total').html(data.html.total)
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

  _updateTaxes: (taxes, html) ->
    for own taxClass, tax of html
      $tax = jQuery(".order_tax_#{taxClass}_field")
      jQuery("label", $tax).html(tax.label)
      jQuery("p", $tax).html(tax.value).show()
      if taxes[taxClass] > 0
        $tax.show()
      else
        $tax.hide()

  _updateTotals: (total, subtotal) ->
    jQuery('#subtotal').html(subtotal)
    jQuery('#total').html(total)

  updateTaxCountry: (e) =>
    jQuery.ajax(@params.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.change_country'
        value: jQuery(e.target).val()
    )
    .done (result) =>
      if result.success == true
        @_updateTotals(result.html.total, result.html.subtotal)
#        @_updateTaxes(result.tax, result.html.tax)

        if result.has_states
          data = []
          for own state, label of result.states
            data.push
              id: state
              text: label
          jQuery("#order_#{@params.tax_field}_state").select2
            data: data
        else
          jQuery("#order_#{@params.tax_field}_state").attr('type', 'text').select2('destroy').val('')

#  updateState: (field) =>
#    @_updateShippingField('jigoshop_cart_change_state', jQuery(field).val())
#
#  updatePostcode: =>
#    @_updateShippingField('jigoshop_cart_change_postcode', jQuery('#postcode').val())
#
#  _updateShippingField: (action, value) =>
#    @block()
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
#      @unblock()

jQuery ->
  new AdminOrder(jigoshop_admin_order)
