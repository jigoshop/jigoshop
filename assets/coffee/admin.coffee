jQuery ($) ->
  $('a[data-toggle=tooltip]').tooltip()
  $('.not-active').closest('tr').hide()
