jQuery ->
  jQuery.fn.jigoshop_media = (options) ->
    frame = false
    settings = jQuery.extend({
      field: jQuery('#media-library-file'),
      thumbnail: false,
      callback: false,
      library: {},
      bind: true
    }, options)

    jQuery(this).on 'jigoshop_media', (e) ->
      e.preventDefault()
      $el = jQuery(e.target)

      # If the media frame already exists, reopen it.
      if frame
        frame.open()
        return

      # Create the media frame.
      frame = wp.media
      # Set the title of the modal.
        title: $el.data('title'),
      # Tell the modal to show only images.
        library: settings.library,
      # Customize the submit button.
        button:
        # Set the text of the button.
          text: $el.data('button')

      # When an image is selected, run a callback.
      frame.on 'select', ->
        # Grab the selected attachment.
        attachment = frame.state().get('selection').first()

        if settings.field
          settings.field.val(attachment.id)

        if settings.thumbnail
          settings.thumbnail
            .attr('src', attachment.changed.url)
            .attr('width', attachment.changed.width)
            .attr('height', attachment.changed.height)

        if typeof(settings.callback) == 'function'
          settings.callback(attachment)

      frame.open()

    if settings.bind
      jQuery(this).on 'click', (event) ->
        event.preventDefault()
        jQuery(event.target).trigger('jigoshop_media')
