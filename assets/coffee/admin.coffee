jQuery ($) ->
  $('a[data-toggle=tooltip]').tooltip()
  $('.not-active').closest('tr').hide()
  delay 3000,  -> $('.settings-error.updated').slideUp ->
    $(this).remove()
  delay 3000,  -> $('.alert-success').slideUp ->
    $(this).remove()
  delay 4000,  -> $('.alert-warning').slideUp ->
    $(this).remove()
  delay 8000,  -> $('.alert-error').slideUp ->
    $(this).remove()
