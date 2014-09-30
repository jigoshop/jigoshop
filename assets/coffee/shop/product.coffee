jQuery ($) ->
  $('a[data-lightbox], img[data-lightbox]').colorbox
    rel: 'product-gallery'
    scalePhotos: true
    preloading: false
    loop: false
    maxWidth: window.innerWidth - 50
    maxHeight: window.innerHeight - 50
