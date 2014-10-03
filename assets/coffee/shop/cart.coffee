jQuery ($) ->
  $('#cart').on 'change', 'input.quantity', ->
    $.ajax(jigoshop.ajax,
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_update_item'
        item: $(this).closest('tr').data('id')
        quantity: $(this).val()
    )
    .done (result) ->
      # TODO
      window.console.dir(result)
