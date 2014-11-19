class TaxSettings
  params:
    new_class: ''
    new_rule: ''

  constructor: (@params) ->
    jQuery('#add-tax-class').on('click', @addNewClass)
    jQuery('#tax-classes').on('click', 'button.remove-tax-class', @removeItem)
    jQuery('#add-tax-rule').on('click', @addNewRule)
    jQuery('#tax-rules')
      .on('click', 'button.remove-tax-rule', @removeItem)
      .on('change', 'select.tax-rule-country', @updateStateField)
    @updateFields()

  removeItem: ->
    jQuery(this).closest('tr').remove()
    return false

  addNewClass: =>
    jQuery('#tax-classes').append(@params.new_class)
    return false

  addNewRule: =>
    $item = jQuery(@params.new_rule)
    jQuery('input.tax-rule-postcodes', $item).select2
      tags: []
      tokenSeparators: [',']
      multiple: true
      formatNoMatches: ''
    jQuery('#tax-rules').append($item)
    return false

  updateStateField: (event) =>
    $parent = jQuery(event.target).closest('tr')
    $states = jQuery('input.tax-rule-states', $parent)
    $country = jQuery('select.tax-rule-country', $parent)
    country = $country.val()
    if @params.states[country]?
      @_attachSelectField($states, @params.states[country])
    else
      @_attachTextField($states)

  updateFields: ->
    jQuery('select.tax-rule-country').change()
    jQuery('input.tax-rule-postcodes').select2
      tags: []
      tokenSeparators: [',']
      multiple: true
      formatNoMatches: ''

  ###
  Attaches Select2 to provided field with proper states to select
  ###
  _attachSelectField: ($field, states) ->
    $field.select2
      data: states
      multiple: true
      initSelection: (element, callback) ->
        data = []
        for value in element.val().split(',')
          text = for state in states when state.id == value
            state
          data.push text[0]
        callback(data)

  ###
  Attaches simple text field to write a state
  ###
  _attachTextField: ($field) ->
    $field.select2('destroy')

jQuery () ->
  new TaxSettings(jigoshop_admin_taxes)
