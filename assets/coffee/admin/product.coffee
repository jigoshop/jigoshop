class AdminProduct
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#add-attribute').on 'click', @addAttribute
#    jQuery('#product-attributes').on 'change', 'input, textarea', => @updateAttribute

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
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_attribute'
        product_id: $parent.closest('.jigoshop').data('id')
        attribute_id: jQuery('#new-attribute').val()
        value: jQuery(event.target).val()
    .done (data) ->
      if data.success? and data.success
        jQuery(data.html).hide().appendTo($parent).slideDown()
      else
        addMessage('danger', data.error, 6000)


jQuery ->
  new AdminProduct(jigoshop_admin_product)
