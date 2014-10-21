class AdminOrder
  params:
    ajax: ''

  constructor: (@params) ->
    @newItemSelect()
    jQuery('#add-item').on 'click', @newItemClick

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
          action: 'jigoshop.admin.find_product'
          }
        results: (data) ->
          if data.success?
            return results: data.results
          return results: []

  newItemClick: (e) =>
    e.preventDefault()
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.order.add_product'
        product: jQuery('#new-item').val()
    .done (data) ->
      window.console.dir data.html
      if data.success?
        $parent = jQuery(e.target).closest('table')
        # TODO: Check if already added - if so - increase quantity only
        jQuery(data.html.row).appendTo($parent)

        jQuery('#product-subtotal', $parent).html(data.html.product_subtotal)
        jQuery('#subtotal').html(data.html.subtotal)
        jQuery('#total').html(data.html.total)
        # TODO: Taxes


jQuery ->
  new AdminOrder(jigoshop_admin_order)
