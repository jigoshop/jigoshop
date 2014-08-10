jQuery ($) ->
  $('#add-tax-class').on 'click', (e) ->
    e.preventDefault()
    $item = $(jigoshop_admin_taxes.new_class).hide()
    $('#tax-classes').append($item)
    $item.slideDown()
  $('#tax-classes').on 'click', 'button.remove-tax-class', (e) ->
    e.preventDefault()
    $(this).parent().slideUp(-> $(this).remove())