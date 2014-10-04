jQuery ($) ->
  $('#cart').on 'change', '.product-quantity input', ->
    $item = $(this).closest('tr')
    $.ajax(jigoshop.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_update_item'
        item: $(this).closest('tr').data('id')
        quantity: $(this).val()
    )
    .done (result) ->
      if result.success == true
        $('.product-subtotal', $item).html(result.html.item_subtotal)
        $('#cart-total > td').html(result.html.total)
      else
        $item.remove()
