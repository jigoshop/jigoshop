class ProductVariable
  VARIATION_EXISTS: 1
  VARIATION_NOT_EXISTS: 2
  VARIATION_NOT_FULL: 3

  params:
    ajax: ''
    variations: {}
  attributes: {}

  constructor: (@params) ->
    jQuery('select.product-attribute').on 'change', @updateAttributes
  updateAttributes: (event) =>
    $buttons = jQuery('#add-to-cart-buttons')
    $messages = jQuery('#add-to-cart-messages')
    results = /(?:^|\s)attributes\[(\d+)](?:\s|$)/g.exec(event.target.name)
    @attributes[results[1]] = event.target.value

    proper = @VARIATION_NOT_FULL
    size = Object.keys(@attributes).length
    for own id, definition of @params.variations
      proper = @VARIATION_EXISTS
      if Object.keys(definition.attributes).length != size
        proper = @VARIATION_NOT_FULL
        continue
      for own attributeId, attributeValue of @attributes
        if definition.attributes[attributeId] != '' and definition.attributes[attributeId] != attributeValue
          proper = @VARIATION_NOT_EXISTS
          break
      if proper == @VARIATION_EXISTS
        if not definition.price
          proper = @VARIATION_NOT_EXISTS
          continue
        jQuery('p.price > span', $buttons).html(definition.html.price)
        jQuery('#variation-id').val(id)
        $buttons.slideDown()
        $messages.slideUp()
        break
    if proper != @VARIATION_EXISTS
      jQuery('#variation-id').val('')
      $buttons.slideUp()
    if proper == @VARIATION_NOT_EXISTS
      $messages.slideDown()

jQuery () ->
  new ProductVariable(jigoshop_product_variable)
