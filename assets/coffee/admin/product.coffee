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
    $parent = jQuery('#product-attributes')
    $item = jQuery(event.target)
    if $item.is('input[type=checkbox]')
      items = jQuery('input[type=checkbox].' + $item.attr('class') + ':checked').toArray()
      item = items.reduce(
        (value, current) ->
          current.value + '|' + value
        ''
      )
    else
      item = $item.val()

    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_attribute'
        product_id: $parent.closest('.jigoshop').data('id')
        attribute_id: $item.closest('li').data('id')
        value: item
    .done (data) =>
      if data.success? and data.success
        addMessage('success', @params.i18n.saved, 2000)
      else
        addMessage('danger', data.error, 6000)
  removeAttribute: (event) =>
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
