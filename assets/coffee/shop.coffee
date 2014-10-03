jQuery ($) ->
  delay 8000,  -> $('.alert-danger').slideUp ->
    $(this).remove()
  delay 4000,  -> $('.alert-success').slideUp ->
    $(this).remove()
