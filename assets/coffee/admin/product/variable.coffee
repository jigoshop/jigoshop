class AdminProductVariable
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#product-type').on 'change', @removeParameters
    jQuery('#add-variation').on 'click', @addVariation

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

jQuery ->
  new AdminProductVariable(jigoshop_admin_product_variable)
