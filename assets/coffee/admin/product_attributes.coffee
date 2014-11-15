class AdminProductAttributes
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#add-attribute').on 'click', @addAttribute
    @$newLabel = jQuery('#attribute-label')
    @$newSlug = jQuery('#attribute-slug')
    @$newType = jQuery('#attribute-type')

  addAttribute: (event) =>
    $parent = jQuery(event.target).closest('tbody')
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product_attributes.add_attribute'
        label: @$newLabel.val()
        slug: @$newSlug.val()
        type: @$newType.val()
    .done (data) =>
      if data.success?
        @$newLabel.val('')
        @$newSlug.val('')
        @$newType.val('multiselect')
        jQuery(data.html.row).appendTo($parent)
      else
        alert data.error # TODO: Nice helper for messages would be good

jQuery ->
  new AdminProductAttributes(jigoshop_admin_product_attributes)
