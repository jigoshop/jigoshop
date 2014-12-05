class AdminCoupon
  params:
    ajax: ''

  constructor: (@params) ->
    @_attachSelectField(jQuery('#jigoshop_coupon_products'), 'jigoshop.admin.product.find')
    @_attachSelectField(jQuery('#jigoshop_coupon_excluded_products'), 'jigoshop.admin.product.find')
    @_attachSelectField(jQuery('#jigoshop_coupon_categories'), 'jigoshop.admin.coupon.find_category')
    @_attachSelectField(jQuery('#jigoshop_coupon_excluded_categories'), 'jigoshop.admin.coupon.find_category')

  ###
  Attaches Select2 to provided field with proper states to select
  ###
  _attachSelectField: ($field, action) =>
    $field.select2
      multiple: true
      minimumInputLength: 3
      ajax:
        url: @params.ajax
        type: 'post'
        dataType: 'json'
        cache: true
        data: (term) ->
          return {
            action: action,
            query: term
          }
        results: (data) ->
          if data.success? and data.success
            return {
              results: data.results
            }
          else
            addMessage('danger', data.error, 6000)
      initSelection: (element, callback) =>
        jQuery.ajax
          url: @params.ajax
          type: 'post'
          dataType: 'json'
          data:
            action: action
            value: jQuery(element).val()
        .done (data) ->
          if data.success? and data.success
            callback(data.results)
          else
            addMessage('danger', data.error, 6000)

jQuery ->
  new AdminCoupon(jigoshop_admin_coupon)
