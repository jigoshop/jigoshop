class AdminProduct
  params:
    ajax: ''
    i18n:
      saved: ''
      confirm_remove: ''
      attribute_removed: ''
    menu: {}

  constructor: (@params) ->
    jQuery('#add-attribute').on 'click', @addAttribute
    jQuery('#product-attributes')
      .on 'change', 'input, select', @updateAttribute
      .on 'click', '.remove-attribute', @removeAttribute
    jQuery('#product-type').on 'change', @changeProductType

    jQuery('.jigoshop_product_data a').on 'click', (e) ->
      e.preventDefault()
      jQuery(this).tab('show')
    jQuery('#stock-manage').on 'change', ->
      if jQuery(this).is(':checked')
        jQuery('.stock-status_field').slideUp()
        jQuery('.stock-status').slideDown()
      else
        jQuery('.stock-status_field').slideDown()
        jQuery('.stock-status').slideUp()
    jQuery('.stock-status_field .not-active').show()
    jQuery('#sales-enabled').on 'change', ->
      if jQuery(this).is(':checked')
        jQuery('.schedule').slideDown()
      else
        jQuery('.schedule').slideUp()
    jQuery('#is_taxable').on 'change', ->
      if jQuery(this).is(':checked')
        jQuery('.tax_classes_field').slideDown()
      else
        jQuery('.tax_classes_field').slideUp()
    jQuery('.tax_classes_field .not-active').show()
    jQuery('#sales-from').datepicker
      todayBtn: 'linked'
      autoclose: true
    jQuery('#sales-to').datepicker
      todayBtn: 'linked'
      autoclose: true

  changeProductType: (event) =>
    type = jQuery(event.target).val()
    jQuery('.jigoshop_product_data li').hide()
    for own tab, visibility of @params.menu
      if visibility == true or type in visibility
        jQuery('.jigoshop_product_data li.' + tab).show()
    jQuery('.jigoshop_product_data li:first a').tab('show')

  addAttribute: (event) =>
    event.preventDefault()
    $parent = jQuery('#product-attributes')
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_attribute'
        product_id: $parent.closest('.jigoshop').data('id')
        attribute_id: jQuery('#new-attribute').val()
    .done (data) ->
      if data.success? and data.success
        jQuery(data.html).hide().appendTo($parent).slideDown()
      else
        addMessage('danger', data.error, 6000)
  updateAttribute: (event) =>
    $container = jQuery('#product-attributes')
    $parent = jQuery(event.target).closest('li.list-group-item')

    items = jQuery('.values input[type=checkbox]:checked', $parent).toArray()
    if items.length
      item = items.reduce(
        (value, current) ->
          current.value + '|' + value
        ''
      )
    else
      item = jQuery('.values select', $parent).val()
      if item == undefined
        item = jQuery('.values input', $parent).val()

    getOptionValue = (current) ->
      if current.type == 'checkbox' or current.type == 'radio'
        return current.checked
      return current.value

    options = {}
    optionsData = jQuery('.options input.attribute-options', $parent).toArray()
    for option in optionsData
      results = /(?:^|\s)product\[attributes]\[\d+]\[(.*?)](?:\s|$)/g.exec(option.name)
      options[results[1]] = getOptionValue(option)

    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_attribute'
        product_id: $container.closest('.jigoshop').data('id')
        attribute_id: $parent.data('id')
        value: item
        options: options
    .done (data) =>
      if data.success? and data.success
        addMessage('success', @params.i18n.saved, 2000)
      else
        addMessage('danger', data.error, 6000)
  removeAttribute: (event) =>
    event.preventDefault()
    if confirm(@params.i18n.confirm_remove)
      $parent = jQuery(event.target).closest('li')
      jQuery.ajax
        url: @params.ajax
        type: 'post'
        dataType: 'json'
        data:
          action: 'jigoshop.admin.product.remove_attribute'
          product_id: $parent.closest('.jigoshop').data('id')
          attribute_id: $parent.data('id')
      .done (data) =>
        if data.success? and data.success
          $parent.slideUp -> $parent.remove()
          addMessage('success', @params.i18n.attribute_removed, 2000)
        else
          addMessage('danger', data.error, 6000)

jQuery ->
  new AdminProduct(jigoshop_admin_product)
