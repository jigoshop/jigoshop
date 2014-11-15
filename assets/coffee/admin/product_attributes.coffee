class AdminProductAttributes
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#add-attribute').on 'click', @addAttribute
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
      if data.success?
        @$newLabel.val('')
        @$newSlug.val('')
        @$newType.val('0')
        jQuery(data.html).appendTo($container)
      else
        alert data.error # TODO: Nice helper for messages would be good

jQuery ->
  new AdminProductAttributes(jigoshop_admin_product_attributes)
