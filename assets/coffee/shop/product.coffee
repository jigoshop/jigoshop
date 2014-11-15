jQuery ($) ->
  $('a[data-lightbox], img[data-lightbox]').colorbox
    rel: 'product-gallery'
    scalePhotos: true
    preloading: false
    loop: false
    maxWidth: window.innerWidth - 50
    maxHeight: window.innerHeight - 50
  $('ul.tabs a').on 'click', (e) ->
    e.preventDefault()
    $(this).tab('show')
