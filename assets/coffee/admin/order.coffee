class AdminOrder
  params:
    ajax: ''

  constructor: (@params) ->
    @newItemSelect()
    jQuery('#add-item').on 'click', @newItemClick
    jQuery('.jigoshop-order table').on 'click', 'a.remove', @removeItemClick
    jQuery('.jigoshop-order table').on 'change', '.price input, .quantity input', @updateItem

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
        jQuery('#subtotal').html(data.html.subtotal)
        jQuery('#total').html(data.html.total)
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
        jQuery('#subtotal').html(data.html.subtotal)
        jQuery('#total').html(data.html.total)
        @_updateTaxes(data.tax, data.html.tax)

  _updateTaxes: (taxes, html) ->
    for own taxClass, tax of html
      $tax = jQuery(".order_tax_#{taxClass}_field")
      jQuery("label", $tax).html(tax.label)
      jQuery("p", $tax).html(tax.value).show()
      if taxes[taxClass] > 0
        $tax.show()
      else
        $tax.hide()

jQuery ->
  new AdminOrder(jigoshop_admin_order)
