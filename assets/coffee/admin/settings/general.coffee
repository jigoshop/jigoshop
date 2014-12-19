class GeneralSettings
  params:
    states: {}

  constructor: (@params) ->
    jQuery('#show_message').on 'change', -> jQuery('#custom_message').closest('tr').toggle()
    jQuery('#custom_message').show().closest('div.form-group').show()

    jQuery('select#country').on 'change', @updateStateField
    @updateFields()

  updateStateField: (event) =>
    $country = jQuery(event.target)
    $states = jQuery('input#state')
    country = $country.val()
    if @params.states[country]?
      @_attachSelectField($states, @params.states[country])
    else
      @_attachTextField($states)

  updateFields: ->
    jQuery('select#country').change()

  ###
  Attaches Select2 to provided field with proper states to select
  ###
  _attachSelectField: ($field, states) ->
    $field.select2
      data: states
      multiple: false

  ###
  Attaches simple text field to write a state
  ###
  _attachTextField: ($field) ->
    $field.select2('destroy')

jQuery () ->
  new GeneralSettings(jigoshop_admin_general)

