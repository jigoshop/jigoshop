jQuery ($) ->
  $('#add-tax-class').on 'click', (e) ->
    e.preventDefault()
    $('#tax-classes').append(jigoshop_admin_taxes.new_class)
  $('#tax-classes').on 'click', 'button.remove-tax-class', (e) ->
    e.preventDefault()
    $(this).closest('tr').remove()