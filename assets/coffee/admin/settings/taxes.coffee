jQuery ($) ->
  $('#add-tax-class').on 'click', (e) ->
    e.preventDefault()
    $('#tax-classes').append(jigoshop_admin_taxes.new_class)
  $('#tax-classes').on 'click', 'button.remove-tax-class', (e) ->
    e.preventDefault()
    $(this).closest('tr').remove()
  $('#add-tax-rule').on 'click', (e) ->
    e.preventDefault()
    $('#tax-rules').append(jigoshop_admin_taxes.new_rule)
  $('#tax-rules').on 'click', 'button.remove-tax-rule', (e) ->
    e.preventDefault()
    $(this).closest('tr').remove()