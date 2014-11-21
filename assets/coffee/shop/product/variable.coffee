class ProductVariable
  params:
    ajax: ''
    variations: {}
  attributes: {}

  constructor: (@params) ->
    jQuery('select.product-attribute').on 'change', @updateAttributes
  updateAttributes: (event) =>
    $buttons = jQuery('#buttons')
    results = /(?:^|\s)attributes\[(\d+)](?:\s|$)/g.exec(event.target.name)
    @attributes[results[1]] = event.target.value

    proper = false
    size = Object.keys(@attributes).length
    for own id, definition of @params.variations
      proper = true
      if Object.keys(definition.attributes).length != size
        proper = false
        continue
      for own attributeId, attributeValue of @attributes
        if definition.attributes[attributeId] != '' and definition.attributes[attributeId] != attributeValue
          proper = false
          break
      if proper
        jQuery('p.price > span', $buttons).html(definition.html.price)
        $buttons.slideDown()
        break
    if not proper
      $buttons.slideUp()

jQuery () ->
  new ProductVariable(jigoshop_product_variable)
