jQuery(function($) {
	"use strict";
	$('#tabs .panel:not(#tabs .panel)').hide();
  $('div#tabs ul.tabs li > a').click(function() {
    var href = $(this).attr('href');
    $('#tabs li').removeClass('active');
    $('div.panel').hide();
    $('div' + href).show();
    $(this).parent().addClass('active');
    return false;
  });
  if ($('#tabs li.active').size()==0) {
    $('#tabs li:first a').click();
  } else {
    $('#tabs li.active a').click();
  }

  $('#review_form_wrapper').hide();
  if (jigoshop_params.load_fancybox) {
    $('a.show_review_form').prettyPhoto( {
      animation_speed: 'normal', /* fast/slow/normal */
      slideshow: 5000, /* false OR interval time in ms */
      autoplay_slideshow: false, /* true/false */
      show_title: false,
      theme: 'pp_default', /* pp_default / light_rounded / dark_rounded / light_square / dark_square / facebook */
      horizontal_padding: 50,
      opacity: 0.7,
      deeplinking: false,
      social_tools: false
    });
  }
  // Star ratings for comments
  $('#rating').hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>');

  $('body').on( 'click', '#respond p.stars a', function() {
    var $star   = $(this);
    var $rating = $(this).closest('#respond').find('#rating');

    $rating.val( $star.text() );
    $star.siblings('a').removeClass('active');
    $star.addClass('active');

    return false;
  }).on( 'click', '#respond #submit', function() {
    var $rating = $(this).closest('#respond').find('#rating');
    var rating  = $rating.val();
    if ( $rating.size() > 0 && ! rating ) {
      alert( jigoshop_params.ratings_message );
      return false;
    }
  });
});
