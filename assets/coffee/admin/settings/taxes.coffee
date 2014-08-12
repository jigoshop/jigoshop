# TODO: Replace this mess with a proper class

jQuery ($) ->
  $('#add-tax-class').on 'click', (e) ->
    e.preventDefault()
    $('#tax-classes').append(jigoshop_admin_taxes.new_class)
  $('#tax-classes').on 'click', 'button.remove-tax-class', (e) ->
    e.preventDefault()
    $(this).closest('tr').remove()
  $('#add-tax-rule').on 'click', (e) ->
    e.preventDefault()
    $item = $(jigoshop_admin_taxes.new_rule)
    $('.tax-rule-postcodes', $item).select2
      tags: []
      tokenSeparators: [',']
      multiple: true
      formatNoMatches: ''
    $('#tax-rules').append($item)
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
        initSelection: (element, callback) ->
          data = []
          for value in element.val().split(',')
            text = for state in jigoshop_admin_taxes.states[$country] when state.id == value
              state
            data.push text[0]
          callback(data)
    else
      $states.select2('destroy').attr('type', 'text')
  $('.tax-rule-country').change()
  $('.tax-rule-postcodes').select2
    tags: []
    tokenSeparators: [',']
    multiple: true
    formatNoMatches: ''