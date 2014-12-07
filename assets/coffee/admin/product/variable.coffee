class AdminProductVariable
  params:
    ajax: ''
    i18n:
      confirm_remove: ''
      variation_removed: ''

  constructor: (@params) ->
    jQuery('#product-type').on 'change', @removeParameters
    jQuery('#add-variation').on 'click', @addVariation
    jQuery('#product-variations')
      .on 'click', '.remove-variation', @removeVariation
      .on 'click', '.show-variation', (event) ->
        $item = jQuery(event.target)
        jQuery('.list-group-item-text', $item.closest('li')).slideToggle ->
          jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')
      .on 'change', 'select.variation-attribute', @updateVariation
      .on 'change', '.list-group-item-text input.form-control, .list-group-item-text select.form-control', @updateVariation

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'variable'
      jQuery('.product_regular_price_field').slideUp()
  addVariation: (event) =>
    event.preventDefault()
    $parent = jQuery('#product-variations')
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.add_variation'
        product_id: $parent.closest('.jigoshop').data('id')
    .done (data) ->
      if data.success? and data.success
        jQuery(data.html).hide().appendTo($parent).slideDown()
      else
        addMessage('danger', data.error, 6000)
  updateVariation: (event) =>
    $container = jQuery('#product-variations')
    $parent = jQuery(event.target).closest('li.list-group-item')

    getOptionValue = (current) ->
      if current.type == 'checkbox' or current.type == 'radio'
        return current.checked
      return current.value

    attributes = {}
    attributesData = jQuery('select.variation-attribute', $parent).toArray()
    for option in attributesData
      results = /(?:^|\s)product\[variation]\[\d+]\[attribute]\[(.*?)](?:\s|$)/g.exec(option.name)
      attributes[results[1]] = getOptionValue(option)

    product = {}
    productData = jQuery('.list-group-item-text input.form-control', $parent).toArray()
    for option in productData
      results = /(?:^|\s)product\[variation]\[\d+]\[product]\[(.*?)](\[(.*?)])?(?:\s|$)/g.exec(option.name)
      if results[3]?
        product[results[1]] = {}
        product[results[1]][results[3]] = getOptionValue(option)
      else
        product[results[1]] = getOptionValue(option)

    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_variation'
        product_id: $container.closest('.jigoshop').data('id')
        variation_id: $parent.data('id')
        attributes: attributes
        product: product
    .done (data) =>
      if data.success? and data.success
        addMessage('success', @params.i18n.saved, 2000)
      else
        addMessage('danger', data.error, 6000)
  removeVariation: (event) =>
    event.preventDefault()
    if confirm(@params.i18n.confirm_remove)
      $parent = jQuery(event.target).closest('li')
      jQuery.ajax
        url: @params.ajax
        type: 'post'
        dataType: 'json'
        data:
          action: 'jigoshop.admin.product.remove_variation'
          product_id: $parent.closest('.jigoshop').data('id')
          variation_id: $parent.data('id')
      .done (data) =>
        if data.success? and data.success
          $parent.slideUp -> $parent.remove()
          addMessage('success', @params.i18n.variation_removed, 2000)
        else
          addMessage('danger', data.error, 6000)

jQuery ->
  new AdminProductVariable(jigoshop_admin_product_variable)
