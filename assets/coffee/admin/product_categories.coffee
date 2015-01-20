class AdminProductCategories
  params:
    category_name: 'product_category'
    placeholder: ''

  constructor: (@params) ->
    $field = jQuery('#' + @params.category_name + '_thumbnail_id')
    $thumbnail = jQuery('#' + @params.category_name + '_thumbnail > img')
    jQuery('#add-image').jigoshop_media(
      field: $field
      thumbnail: $thumbnail
      callback: ->
        if $field.val() != ''
          jQuery('#remove-image').show()
      library:
        type: 'image'
    )
    jQuery('#remove-image').on 'click', (e) =>
      e.preventDefault()
      $field.val('')
      $thumbnail.attr('src', @params.placeholder)
      jQuery(e.target).hide()

jQuery ->
  new AdminProductCategories(jigoshop_admin_product_categories)
