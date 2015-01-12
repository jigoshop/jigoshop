class CheckoutPay
  params:
    assets: ''

  constructor: (@params) ->
    jQuery.fn.payment = @payment.bind(@, @params)
    jQuery('#payment-methods').on 'change', 'li input[type=radio]', ->
      jQuery('#payment-methods li > div').slideUp()
      jQuery('div', jQuery(this).closest('li')).slideDown()

  payment: (params, options) ->
    settings = jQuery.extend {
      redirect: 'Redirecting...'
      message: 'Thank you for your order. We are now redirecting you to make payment.'
    }, options

    jQuery(document.body).block
      message: '<img src="' + params.assets + '/images/loading.gif" alt="' + settings.redirect + '" />' + settings.message
      css:
        padding: '20px'
        width: 'auto'
        height: 'auto'
        border: '1px solid #83AC31'
      overlayCss:
        opacity: 0.01

    this.submit();

jQuery ->
  new CheckoutPay(jigoshop_checkout_pay)
