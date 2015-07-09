class AdminEmail
  params:
    ajax: ''

  constructor: (@params) ->
    jQuery('#jigoshop_email_actions').on 'change', @updateVariables
  updateVariables: (event) =>
    event.preventDefault()
    $parent = jQuery(event.target).closest('div.jigoshop')
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.email.update_variable_list'
        email: $parent.data('id')
        actions: jQuery(event.target).val()
    .done (data) ->
      if data.success? and data.success
        jQuery('#available_arguments').replaceWith(data.html)
      else
        addMessage('danger', data.error, 6000)

jQuery ->
  new AdminEmail(jigoshop_admin_email)
