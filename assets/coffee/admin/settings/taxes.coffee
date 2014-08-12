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
  $('#tax-rules')
  .on 'click', 'button.remove-tax-rule', (e) ->
    e.preventDefault()
    $(this).closest('tr').remove()
  .on 'change', '.tax-rule-country', ->
    $parent = $(this).closest('tr')
    $states = $('.tax-rule-states', $parent)
    $country = $('option:selected', $(this)).val()
    if jigoshop_admin_taxes.states[$country]?
      $states.attr('type', 'hidden')
      $states.select2
        data:
          results: jigoshop_admin_taxes.states[$country]
          text: 'text'
        multiple: true
    else
      $states.select2('destroy').attr('type', 'text')
  $('.tax-rule-country').change()