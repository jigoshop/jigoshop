jQuery ($) ->
  $('#cart').on 'change', 'input.quantity', ->
    cart = {}
    cart[$(this).closest('tr').data('id')] = $(this).val()

    $.post(jigoshop.ajax,
      dataType: 'json'
      data:
        action: 'jigoshop_update_cart'
        cart: cart
    )
    .done (result) ->
      # TODO
      window.console.dir(result)
