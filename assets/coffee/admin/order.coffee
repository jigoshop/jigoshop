class AdminOrder
  params:
    ajax: ''

  constructor: (@params) ->
    @newItemSelect()
    jQuery('#add-item').on 'click', @newItemClick
    jQuery('.jigoshop-order table').on 'click', 'a.remove', @removeItemClick

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
    $parent = jQuery(e.target).closest('table')

    # TODO: Check if already added - if so - increase quantity only
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.add_product'
        product: jQuery('#new-item').val()
        order: $parent.data('order')
    .done (data) ->
      if data.success?
        jQuery(data.html.row).appendTo($parent)
        jQuery('#product-subtotal', $parent).html(data.html.product_subtotal)
        jQuery('#subtotal').html(data.html.subtotal)
        jQuery('#total').html(data.html.total)
        # TODO: Taxes

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
    .done (data) ->
      if data.success?
        $row.remove()
        jQuery('#product-subtotal', $parent).html(data.html.product_subtotal)
        jQuery('#subtotal').html(data.html.subtotal)
        jQuery('#total').html(data.html.total)
# TODO: Taxes

jQuery ->
  new AdminOrder(jigoshop_admin_order)
