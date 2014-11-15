delay = (time, callback) -> setTimeout callback, time
addMessage = (type, message) ->
  $alert = jQuery(document.createElement('div')).attr('class', "alert alert-#{type}").html(message).hide()
  $alert.appendTo(jQuery('#messages'))
  $alert.slideDown()
  delay 2000, ->
    $alert.slideUp ->
      $alert.remove()
