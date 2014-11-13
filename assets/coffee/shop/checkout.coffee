class Checkout
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#different_shipping').on 'change', -> jQuery('#shipping-address').slideToggle()
    jQuery('#payment-methods').on 'change', 'li input[type=radio]', ->
      jQuery('#payment-methods li > div').slideUp()
      jQuery('div', jQuery(this).closest('li')).slideDown()

    # TODO: Copy shipping changing etc. here from Cart
    # TODO: Refactor Cart and Checkout (for sure) to create one place for many shared parameters and functions

jQuery ->
  new Checkout(jigoshop_checkout)
