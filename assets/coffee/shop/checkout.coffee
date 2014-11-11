class Checkout
  params:
    ajax: ''
    assets: ''

  constructor: (@params) ->
    jQuery('#different_shipping').on 'change', ->
      jQuery('#shipping-address').slideToggle()

jQuery ->
  new Checkout(jigoshop_checkout)
