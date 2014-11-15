class AdminProductAttributes
  params:
    ajax: ''
    i18n:
      saved: ''

  constructor: (@params) ->
    jQuery('#add-attribute').on 'click', @addAttribute
    jQuery('table#product-attributes')
      .on 'change', 'input, select', @updateAttribute
    @$newLabel = jQuery('#attribute-label')
    @$newSlug = jQuery('#attribute-slug')
    @$newType = jQuery('#attribute-type')

  addAttribute: (event) =>
    $container = jQuery('tbody', jQuery(event.target).closest('table'))
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product_attributes.save'
        label: @$newLabel.val()
        slug: @$newSlug.val()
        type: @$newType.val()
    .done (data) =>
      if data.success? and data.success
        @$newLabel.val('')
        @$newSlug.val('')
        @$newType.val('0')
        jQuery(data.html).appendTo($container)
      else
        addMessage('danger', data.error)
  updateAttribute: (event) =>
    $parent = jQuery(event.target).closest('tr')
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product_attributes.save'
        id: $parent.data('id')
        label: jQuery('input.attribute-label', $parent).val()
        slug: jQuery('input.attribute-slug', $parent).val()
        type: jQuery('select.attribute-type', $parent).val()
    .done (data) =>
      if data.success? and data.success
        $parent.replaceWith(data.html)
        addMessage('success', @params.i18n.saved)
      else
        addMessage('danger', data.error)

jQuery ->
  new AdminProductAttributes(jigoshop_admin_product_attributes)
