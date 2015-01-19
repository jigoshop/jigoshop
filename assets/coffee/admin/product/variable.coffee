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
      .on 'click', '.set_variation_image', @setImage
      .on 'click', '.remove_variation_image', @removeImage
    jQuery('.set_variation_image').each @connectImage

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
        jQuery(data.html).hide().appendTo($parent).slideDown().trigger('jigoshop.variation.add')
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
        $parent.trigger('jigoshop.variation.update')
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
          $parent.trigger('jigoshop.variation.remove')
          $parent.slideUp -> $parent.remove()
          addMessage('success', @params.i18n.variation_removed, 2000)
        else
          addMessage('danger', data.error, 6000)
  connectImage: (index, element) =>
    $element = jQuery(element)
    $remove = $element.next('.remove_variation_image')
    $thumbnail = jQuery('img', $element.parent())
    $element.jigoshop_media(
      field: false
      bind: false
      thumbnail: $thumbnail
      callback: (attachment) =>
        $remove.show()
        jQuery.ajax
          url: @params.ajax
          type: 'post'
          dataType: 'json'
          data:
            action: 'jigoshop.admin.product.set_variation_image'
            product_id: $element.closest('.jigoshop').data('id')
            variation_id: $element.closest('.variation').data('id')
            image_id: attachment.id
        .done (data) ->
          if !data.success? or !data.success
            addMessage('danger', data.error, 6000)
      library:
        type: 'image'
    )
  setImage: (event) ->
    event.preventDefault()
    jQuery(event.target).trigger('jigoshop_media')
  removeImage: (event) =>
    event.preventDefault()
    $element = jQuery(event.target)
    $thumbnail = jQuery('img', $element.parent())
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.set_variation_image'
        product_id: $element.closest('.jigoshop').data('id')
        variation_id: $element.closest('.variation').data('id')
        image_id: -1
    .done (data) ->
      if data.success? and data.success
        $thumbnail
          .attr('src', data.url)
          .attr('width', 150)
          .attr('height', 150)
        $element.hide()
      else
        addMessage('danger', data.error, 6000)

jQuery ->
  new AdminProductVariable(jigoshop_admin_product_variable)
