class AdminProductDownloadable
  constructor: ->
    jQuery('#product-variations > li').on 'change', '.variation-type', @removeVariationParameters

  removeVariationParameters: (event) ->
    $item = jQuery(event.target)
    $parent = $item.closest('li.variation')
    if $item.val() == 'downloadable'
      jQuery('.product-downloadable', $parent).slideDown()
    else
      jQuery('.product-downloadable', $parent).slideUp()

jQuery ->
  new AdminProductDownloadable()
