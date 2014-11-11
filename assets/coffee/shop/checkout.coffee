class Checkout
  params:
    ajax: ''
    assets: ''

  constructor: (@params) ->
    jQuery('#different_shipping').on 'change', -> jQuery('#shipping-address').slideToggle()
    jQuery('#payment-methods').on 'change', 'li input[type=radio]', ->
      jQuery('#payment-methods li > div.well').slideUp()
      jQuery('div.well', jQuery(this).closest('li')).slideDown()

jQuery ->
  new Checkout(jigoshop_checkout)
