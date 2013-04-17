<?php
/**
 * Functions used in template files
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Front page archive/shop template
 */
if (!function_exists('jigoshop_front_page_archive')) {
	function jigoshop_front_page_archive() {

		global $paged;

		// TODO: broken
		// is_page() fails for this - still testing -JAP-
		// is_shop() works, but only with a [recent_products] shortcode on the Shop page
		// however, if shortcode is used when not front page, double product listings appear
		//
		if ( is_front_page() && is_page( jigoshop_get_page_id('shop') )) :
//		if ( is_front_page() && is_shop() ) :

			if ( get_query_var('paged') ) {
			    $paged = get_query_var('paged');
			} else if ( get_query_var('page') ) {
			    $paged = get_query_var('page');
			} else {
			    $paged = 1;
			}

			query_posts( array( 'page_id' => '', 'post_type' => 'product', 'paged' => $paged ) );

			define('SHOP_IS_ON_FRONT', true);

		endif;
	}
}
add_action('wp_head', 'jigoshop_front_page_archive', 0);


/**
 * Content Wrappers
 **/
if (!function_exists('jigoshop_output_content_wrapper')) {
	function jigoshop_output_content_wrapper() {
		if ( get_option('template') === 'twentytwelve' ) echo '<div id="primary" class="site-content"><div id="content" role="main">';
		elseif ( get_option('template') === 'twentyeleven' ) echo '<section id="primary"><div id="content" role="main">';
		else echo '<div id="container"><div id="content" role="main">';  /* twenty-ten */
	}
}
if (!function_exists('jigoshop_output_content_wrapper_end')) {
	function jigoshop_output_content_wrapper_end() {
		if ( get_option('template') === 'twentytwelve' ) echo '</div></div>';
		elseif ( get_option('template') === 'twentyeleven' ) echo  '</div></section>';
		else echo '</div></div>'; /* twenty-ten */
	}
}

/**
 * Sale Flash
 **/
if (!function_exists('jigoshop_show_product_sale_flash')) {
	function jigoshop_show_product_sale_flash( $post, $_product ) {
		if ($_product->is_on_sale()) echo '<span class="onsale">'.__('Sale!', 'jigoshop').'</span>';
	}
}

/**
 * Sidebar
 **/
if (!function_exists('jigoshop_get_sidebar')) {
	function jigoshop_get_sidebar() {
		get_sidebar('shop');
	}
}

/**
 * Products Loop
 **/
if (!function_exists('jigoshop_template_loop_add_to_cart')) {
	function jigoshop_template_loop_add_to_cart( $post, $_product ) {

		do_action('jigoshop_before_add_to_cart_button');

		// do not show "add to cart" button if product's price isn't announced
		if ( $_product->get_price() === '' AND ! ($_product->is_type(array('variable', 'grouped', 'external'))) ) return;

		if ( $_product->is_in_stock() OR $_product->is_type('external') ) {
			$button_type = Jigoshop_Base::get_options()->get_option('jigoshop_catalog_product_button');
			if ( $button_type === false ) $button_type = 'add';
			if ( $_product->is_type(array('variable', 'grouped')) ) {
				if ( $button_type != 'none' ) {
					if ( $button_type == 'view' ) {
						$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'.__('View Product', 'jigoshop').'</a>';
					} else {
						$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'._x('Select', 'verb', 'jigoshop').'</a>';
					}
				} else {
					$output = '';
				}
			} else if ( $_product->is_type('external') ) {
				if ( $button_type != 'none' ) {
					if ( $button_type == 'view' ) {
						$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'.__('View Product', 'jigoshop').'</a>';
					} else {
						$output = '<a href="'.esc_url(get_post_meta( $_product->id, 'external_url', true )).'" class="button" rel="nofollow">'.__('Buy product', 'jigoshop').'</a>';
					}
				} else {
					$output = '';
				}
			} else if ( $button_type == 'add' ) {
				$output = '<a href="'.esc_url($_product->add_to_cart_url()).'" class="button" rel="nofollow">'.__('Add to cart', 'jigoshop').'</a>';
			} else if ( $button_type == 'view' ) {
				$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'.__('View Product', 'jigoshop').'</a>';
			} else {
				$output = '';
			}
		} else if ( ($_product->is_type(array('grouped')) ) ) {
			$output = '';
		} else {
			$output = '<span class="nostock">'.__('Out of Stock', 'jigoshop').'</span>';
		}
		
		echo apply_filters( 'jigoshop_loop_add_to_cart_output', $output, $post, $_product );

		do_action('jigoshop_after_add_to_cart_button');

	}
}
if (!function_exists('jigoshop_template_loop_product_thumbnail')) {
	function jigoshop_template_loop_product_thumbnail( $post, $_product ) {
		echo jigoshop_get_product_thumbnail();
	}
}
if (!function_exists('jigoshop_template_loop_price')) {
	function jigoshop_template_loop_price( $post, $_product ) {
		?><span class="price"><?php echo $_product->get_price_html(); ?></span><?php
	}
}

/**
 * Before Single Products Summary Div
 **/
if (!function_exists('jigoshop_show_product_images')) {
	function jigoshop_show_product_images() {

		global $_product, $post;

		echo '<div class="images">';

		do_action( 'jigoshop_before_single_product_summary_thumbnails', $post, $_product );

		$thumb_id = 0;
		if (has_post_thumbnail()) :
			$thumb_id = get_post_thumbnail_id();
			// since there are now user settings for sizes, shouldn't need filters -JAP-
			//$large_thumbnail_size = apply_filters('single_product_large_thumbnail_size', 'shop_large');
			$large_thumbnail_size = jigoshop_get_image_size( 'shop_large' );
			$image_classes = apply_filters( 'jigoshop_product_image_classes', array(), $_product );
			array_unshift( $image_classes, 'zoom' );
			$image_classes = implode( ' ', $image_classes );
			echo '<a href="'.wp_get_attachment_url($thumb_id).'" class="'.$image_classes.'" rel="thumbnails">';
			the_post_thumbnail($large_thumbnail_size);
			echo '</a>';
		else :
			echo jigoshop_get_image_placeholder( 'shop_large' );
		endif;

		do_action('jigoshop_product_thumbnails');

		echo '</div>';

	}
}
if (!function_exists('jigoshop_show_product_thumbnails')) {
	function jigoshop_show_product_thumbnails() {

		global $_product, $post;

		echo '<div class="thumbnails">';

		$thumb_id = get_post_thumbnail_id();
		$small_thumbnail_size = jigoshop_get_image_size( 'shop_thumbnail' );

		$args = array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post->ID, 'orderby' => 'menu_order', 'order' => 'asc' );

		$attachments = get_posts($args);
		if ($attachments) :
			$loop = 0;
			$columns = Jigoshop_Base::get_options()->get_option( 'jigoshop_product_thumbnail_columns', '3' );
			$columns = apply_filters( 'single_thumbnail_columns', $columns );
			foreach ( $attachments as $attachment ) :

				if ($thumb_id==$attachment->ID) continue;

				$loop++;

				$_post =  get_post( $attachment->ID );
				$url = wp_get_attachment_url($_post->ID);
				$post_title = esc_attr($_post->post_title);
				$image = wp_get_attachment_image($attachment->ID, $small_thumbnail_size);

				if ( ! $image || $url == get_post_meta($post->ID, 'file_path', true) )
					continue;

				echo '<a href="'.esc_url($url).'" title="'.esc_attr($post_title).'" rel="thumbnails" class="zoom ';
				if ($loop==1 || ($loop-1)%$columns==0) echo 'first';
				if ($loop%$columns==0) echo 'last';
				echo '">'.$image.'</a>';

			endforeach;
		endif;
		wp_reset_query();

		echo '</div>';

	}
}

/**
 * After Single Products Summary Div
 **/
if (!function_exists('jigoshop_output_product_data_tabs')) {
	function jigoshop_output_product_data_tabs() {

		if (isset($_COOKIE["current_tab"])) $current_tab = $_COOKIE["current_tab"]; else $current_tab = '#tab-description';

		?>
		<div id="tabs">
			<ul class="tabs">

				<?php do_action('jigoshop_product_tabs', $current_tab); ?>

			</ul>

			<?php do_action('jigoshop_product_tab_panels'); ?>

		</div>
		<?php

	}
}

/**
 * Product summary box
 **/
if (!function_exists('jigoshop_template_single_title')) {
	function jigoshop_template_single_title( $post, $_product ) {
		?><h1 class="product_title page-title"><?php echo apply_filters( 'jigoshop_single_product_title', the_title( '', '', false ) ); ?></h1><?php
	}
}

if (!function_exists('jigoshop_template_single_price')) {
	function jigoshop_template_single_price( $post, $_product ) {
		?><p class="price"><?php echo apply_filters( 'jigoshop_single_product_price', $_product->get_price_html() ); ?></p><?php
	}
}

if (!function_exists('jigoshop_template_single_excerpt')) {
	function jigoshop_template_single_excerpt( $post, $_product ) {
		if ($post->post_excerpt) echo apply_filters( 'jigoshop_single_product_excerpt', wpautop(wptexturize($post->post_excerpt)) );
	}
}

if (!function_exists('jigoshop_template_single_meta')) {
	function jigoshop_template_single_meta( $post, $_product ) {

        $jigoshop_options = Jigoshop_Base::get_options();
		echo '<div class="product_meta">';
		if ($jigoshop_options->get_option('jigoshop_enable_sku')=='yes' && !empty($_product->sku)) :
			echo '<div class="sku">'.__('SKU','jigoshop').': ' . $_product->sku . '</div>';
		endif;

		echo $_product->get_categories( ', ', ' <div class="posted_in">' . __( 'Posted in ', 'jigoshop' ) . '', '.</div>');
		echo $_product->get_tags( ', ', ' <div class="tagged_as">' . __( 'Tagged as ', 'jigoshop' ) . '', '.</div>');
		echo '</div>';

	}
}

if (!function_exists('jigoshop_template_single_sharing')) {
	function jigoshop_template_single_sharing( $post, $_product ) {

        $jigoshop_options = Jigoshop_Base::get_options();
		if ($jigoshop_options->get_option('jigoshop_sharethis')) :
			echo '<div class="social">
				<iframe src="https://www.facebook.com/plugins/like.php?href='.urlencode(get_permalink($post->ID)).'&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
				<span class="st_twitter"></span><span class="st_email"></span><span class="st_sharethis"></span><span class="st_plusone_button"></span>
			</div>';
		endif;

	}
}

/**
 * Product Add to cart buttons
 **/
if (!function_exists('jigoshop_template_single_add_to_cart')) {
	function jigoshop_template_single_add_to_cart( $post, $_product ) {
		$availability = $_product->get_availability();
		if ( $availability <> '' ) {
			?>
			<p class="stock <?php echo $availability['class'] ?>"><?php echo $availability['availability']; ?></p>
			<?php
		}
		if ( $_product->is_in_stock() ) {
			do_action( $_product->product_type . '_add_to_cart' );
		}

	}
}
if (!function_exists('jigoshop_simple_add_to_cart')) {
	function jigoshop_simple_add_to_cart() {

		global $_product; $availability = $_product->get_availability();

		// do not show "add to cart" button if product's price isn't announced
		if( $_product->get_price() === '') return;

		?>
		<form action="<?php echo esc_url( $_product->add_to_cart_url() ); ?>" class="cart" method="post">
			<?php do_action('jigoshop_before_add_to_cart_form_button'); ?>
		 	<div class="quantity"><input name="quantity" value="1" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>
		 	<button type="submit" class="button-alt"><?php _e('Add to cart', 'jigoshop'); ?></button>
		 	<?php do_action('jigoshop_add_to_cart_form'); ?>
		</form>
		<?php
	}
}
if (!function_exists('jigoshop_virtual_add_to_cart')) {
	function jigoshop_virtual_add_to_cart() {

		jigoshop_simple_add_to_cart();

	}
}
if (!function_exists('jigoshop_downloadable_add_to_cart')) {
	function jigoshop_downloadable_add_to_cart() {

		global $_product; $availability = $_product->get_availability();

		// do not show "add to cart" button if product's price isn't announced
		if( $_product->get_price() === '') return;

		?>
		<form action="<?php echo esc_url( $_product->add_to_cart_url() ); ?>" class="cart" method="post">
			<?php do_action('jigoshop_before_add_to_cart_form_button'); ?>
			<button type="submit" class="button-alt"><?php _e('Add to cart', 'jigoshop'); ?></button>
			<?php do_action('jigoshop_add_to_cart_form'); ?>
		</form>
		<?php
	}
}
if (!function_exists('jigoshop_grouped_add_to_cart')) {
	function jigoshop_grouped_add_to_cart() {

		global $_product;
		if(!$_product->get_children()) return;
		?>
		<form action="<?php echo esc_url( $_product->add_to_cart_url() ); ?>" class="cart" method="post">
			<table>
				<tbody>
					<?php foreach ($_product->get_children() as $child_ID) : $child = $_product->get_child($child_ID); $cavailability = $child->get_availability(); ?>
						<tr>
							<td><div class="quantity"><input name="quantity[<?php echo $child->ID; ?>]" value="0" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div></td>
							<td><label for="product-<?php echo $child->id; ?>"><?php
								if ($child->is_visible()) echo '<a href="'.get_permalink($child->ID).'">';
								echo $child->get_title();
								if ($child->is_visible()) echo '</a>';
							?></label></td>
							<td class="price"><?php echo $child->get_price_html(); ?><small class="stock <?php echo $cavailability['class'] ?>"><?php echo $cavailability['availability']; ?></small></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
      <?php do_action('jigoshop_before_add_to_cart_form_button'); ?>			
			<button type="submit" class="button-alt"><?php _e('Add to cart', 'jigoshop'); ?></button>
			<?php do_action('jigoshop_add_to_cart_form'); ?>
		</form>
		<?php
	}
}
if (!function_exists('jigoshop_variable_add_to_cart')) {
	function jigoshop_variable_add_to_cart() {

		global $post, $_product;
        $jigoshop_options = Jigoshop_Base::get_options();
		
		$attributes = $_product->get_available_attributes_variations();

        //get all variations available as an array for easy usage by javascript
        $variationsAvailable = array();
        $children = $_product->get_children();

        foreach($children as $child) {
            /* @var $variation jigoshop_product_variation */
            $variation = $_product->get_child( $child );
            if($variation instanceof jigoshop_product_variation) {
                $vattrs = $variation->get_variation_attributes();
                $availability = $variation->get_availability();

                //@todo needs to be moved to jigoshop_product_variation class
                if (has_post_thumbnail($variation->get_variation_id())) {
                    $attachment_id = get_post_thumbnail_id( $variation->get_variation_id() );
                    $large_thumbnail_size = apply_filters('single_product_large_thumbnail_size', 'shop_large');
                    $image = wp_get_attachment_image_src( $attachment_id, $large_thumbnail_size);
                    if ( ! empty( $image ) ) $image = $image[0];
                    $image_link = wp_get_attachment_image_src( $attachment_id, 'full');
                    if ( ! empty( $image_link ) ) $image_link = $image_link[0];
                } else {
                    $image = '';
                    $image_link = '';
                }

				$a_weight = $a_length = $a_width = $a_height = '';

                if ( $variation->get_weight() ) {
                	$a_weight = '
                    	<tr class="weight">
                    		<th>Weight</th>
                    		<td>'.$variation->get_weight().$jigoshop_options->get_option('jigoshop_weight_unit').'</td>
                    	</tr>';
            	}

            	if ( $variation->get_length() ) {
	            	$a_length = '
	                	<tr class="length">
	                		<th>Length</th>
	                		<td>'.$variation->get_length().$jigoshop_options->get_option('jigoshop_dimension_unit').'</td>
	                	</tr>';
                }

                if ( $variation->get_width() ) {
	                $a_width = '
	                	<tr class="width">
	                		<th>Width</th>
	                		<td>'.$variation->get_width().$jigoshop_options->get_option('jigoshop_dimension_unit').'</td>
	                	</tr>';
                }

                if ( $variation->get_height() ) {
	                $a_height = '
	                	<tr class="height">
	                		<th>Height</th>
	                		<td>'.$variation->get_height().$jigoshop_options->get_option('jigoshop_dimension_unit').'</td>
	                	</tr>
	                ';
            	}

                $variationsAvailable[] = array(
					'variation_id'     => $variation->get_variation_id(),
					'sku'              => '<div class="sku">'.__('SKU','jigoshop').': ' . $variation->get_sku() . '</div>',
					'attributes'       => $vattrs,
					'in_stock'         => $variation->is_in_stock(),
					'image_src'        => $image,
					'image_link'       => $image_link,
					'price_html'       => '<span class="price">'.$variation->get_price_html().'</span>',
					'availability_html'=> '<p class="stock ' . esc_attr( $availability['class'] ) . '">'. $availability['availability'].'</p>',
					'a_weight'         => $a_weight,
					'a_length'         => $a_length,
					'a_width'          => $a_width,
					'a_height'         => $a_height,
                );
            }
        }

		?>
        <script type="text/javascript">
            var product_variations = <?php echo json_encode($variationsAvailable) ?>;
        </script>
        <?php $default_attributes = $_product->get_default_attributes() ?>
		<form action="<?php echo esc_url( $_product->add_to_cart_url() ); ?>" class="variations_form cart" method="post">
			<fieldset class="variations">
				<?php foreach ( $attributes as $name => $options ): ?>
					<?php $sanitized_name = sanitize_title( $name ); ?>
					<div>
						<span class="select_label"><?php echo jigoshop_product::attribute_label('pa_'.$name); ?></span>
						<select id="<?php echo esc_attr( $sanitized_name ); ?>" name="tax_<?php echo $sanitized_name; ?>">
							<option value=""><?php echo __('Choose an option ', 'jigoshop') ?>&hellip;</option>
							
							<?php if ( empty( $_POST ) ): ?>
									<?php $selected_value = ( isset( $default_attributes[ $sanitized_name ] ) ) ? $default_attributes[ $sanitized_name ] : ''; ?>
							<?php else: ?>
									<?php $selected_value = isset( $_POST[ 'tax_' . $sanitized_name ] ) ? $_POST[ 'tax_' . $sanitized_name ] : ''; ?>
							<?php endif; ?>

							<?php foreach ( $options as $value ) : ?>
								<?php if ( taxonomy_exists( 'pa_'.$sanitized_name )) : ?>
									<?php $term = get_term_by( 'slug', $value, 'pa_'.$sanitized_name ); ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_value, $term->slug) ?>><?php echo $term->name; ?></option>
								<?php else : ?>
									<option value="<?php echo esc_attr( sanitize_title( $value ) ); ?>"<?php selected( $selected_value, sanitize_title( $value )) ?> ><?php echo $value; ?></option>
								<?php endif;?>
							<?php endforeach; ?>
						</select>
					</div>
                <?php endforeach;?>
			</fieldset>
			<div class="single_variation"></div>
			<?php do_action('jigoshop_before_add_to_cart_form_button'); ?>
			<div class="variations_button" style="display:none;">
                <input type="hidden" name="variation_id" value="" />
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
                <div class="quantity"><input name="quantity" value="1" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>
				<input type="submit" class="button-alt" value="<?php esc_html_e('Add to cart', 'jigoshop'); ?>" />
			</div>
			<?php do_action('jigoshop_add_to_cart_form'); ?>
		</form>
		<?php
	}
}

if (!function_exists('jigoshop_external_add_to_cart')) {
	function jigoshop_external_add_to_cart() {
		global $_product;
		$external_url = get_post_meta( $_product->ID, 'external_url', true );

		if ( ! $external_url ) return false;
			
		?>
			<form action="" class="cart" method="">
				<?php do_action('jigoshop_before_add_to_cart_form_button'); ?>
				<p>
					<a href="<?php echo esc_url( $external_url ); ?>" rel="nofollow" class="button"><?php _e('Buy product', 'jigoshop'); ?></a>
				</p>
				<?php do_action('jigoshop_add_to_cart_form'); ?>
			</form>
		<?php
	}
}


/**
 * Product Add to Cart forms
 **/
if (!function_exists('jigoshop_add_to_cart_form_nonce')) {
	function jigoshop_add_to_cart_form_nonce() {
		jigoshop::nonce_field('add_to_cart');
	}
}

/**
 * Pagination
 **/
if (!function_exists('jigoshop_pagination')) {
	function jigoshop_pagination() {

		global $wp_query;

		if (  $wp_query->max_num_pages > 1 ) :
			?>
			<div class="navigation">
				<?php if ( function_exists( 'wp_pagenavi' )) : ?>
					<?php wp_pagenavi(); ?>
				<?php else : ?>
					<div class="nav-next"><?php next_posts_link( __( 'Next <span class="meta-nav">&rarr;</span>', 'jigoshop' ) ); ?></div>
					<div class="nav-previous"><?php previous_posts_link( __( '<span class="meta-nav">&larr;</span> Previous', 'jigoshop' ) ); ?></div>
				<?php endif; ?>
			</div>
			<?php
		endif;

	}
}

/**
 * Product page tabs
 **/
if (!function_exists('jigoshop_product_description_tab')) {
	function jigoshop_product_description_tab( $current_tab ) {
		global $post;
		if( ! $post->post_content )
			return false;
		?>
		<li <?php if ($current_tab=='#tab-description') echo 'class="active"'; ?>><a href="#tab-description"><?php _e('Description', 'jigoshop'); ?></a></li>
		<?php
	}
}
if (!function_exists('jigoshop_product_attributes_tab')) {
	function jigoshop_product_attributes_tab( $current_tab ) {

		global $_product;
		if( ( $_product->has_attributes() || $_product->has_dimensions() || $_product->has_weight() ) ):
		?>
		<li <?php if ($current_tab=='#tab-attributes') echo 'class="active"'; ?>><a href="#tab-attributes"><?php _e('Additional Information', 'jigoshop'); ?></a></li><?php endif;

	}
}
if (!function_exists('jigoshop_product_reviews_tab')) {
	function jigoshop_product_reviews_tab( $current_tab ) {

		if ( comments_open() ) : ?><li <?php if ($current_tab=='#tab-reviews') echo 'class="active"'; ?>><a href="#tab-reviews"><?php _e('Reviews', 'jigoshop'); ?><?php echo comments_number(' (0)', ' (1)', ' (%)'); ?></a></li><?php endif;

	}
}
if (!function_exists('jigoshop_product_customize_tab')) {
	function jigoshop_product_customize_tab( $current_tab ) {

		global $_product;

		if ( get_post_meta( $_product->ID , 'customizable', true ) == 'yes' ) {
			?>
			<li <?php if ($current_tab=='#tab-customize') echo 'class="active"'; ?>><a href="#tab-customize"><?php _e('Personalize', 'jigoshop'); ?></a></li>
			<?php

		}

	}
}

/**
 * Product page tab panels
 **/
if (!function_exists('jigoshop_product_description_panel')) {
	function jigoshop_product_description_panel() {
		$content = get_the_content();
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = apply_filters( 'jigoshop_single_product_content', $content );
		if ( $content <> '' ) {
			echo '<div class="panel" id="tab-description">';
			$heading = apply_filters('jigoshop_product_description_heading', '');
			if ( ! empty( $heading )) {
				echo '<h2>' . $heading . '</h2>';
			}
			echo $content;
			echo '</div>';
		}
	}
}
if (!function_exists('jigoshop_product_attributes_panel')) {
	function jigoshop_product_attributes_panel() {
		global $_product;
		$content = apply_filters( 'jigoshop_single_product_attributes', $_product->list_attributes() );
		if ( $content <> '' ) {
			echo '<div class="panel" id="tab-attributes">';
			$heading = apply_filters('jigoshop_product_attributes_heading', '');
			if ( ! empty( $heading )) {
				echo '<h2>' . $heading . '</h2>';
			}
			echo $content;
			echo '</div>';
		}
	}
}
if (!function_exists('jigoshop_product_reviews_panel')) {
	function jigoshop_product_reviews_panel() {
		echo '<div class="panel" id="tab-reviews">';
		comments_template();
		echo '</div>';
	}
}
if (!function_exists('jigoshop_product_customize_panel')) {
	function jigoshop_product_customize_panel() {
		global $_product;

		if ( isset( $_POST['Submit'] ) && $_POST['Submit'] == __('Save Personalization', 'jigoshop') ) {
			$custom_products = (array) jigoshop_session::instance()->customized_products;
			$custom_products[$_POST['customized_id']] = trim( wptexturize( $_POST['jigoshop_customized_product'] ));
			jigoshop_session::instance()->customized_products = $custom_products;
		}

		if ( get_post_meta( $_product->ID , 'customizable', true ) == 'yes' ) :
			$custom_products = (array) jigoshop_session::instance()->customized_products;
			$custom = isset( $custom_products[$_product->ID] ) ? $custom_products[$_product->ID] : '';
			$custom_length = get_post_meta( $_product->ID , 'customized_length', true );
			$length_str = $custom_length == '' ? '' : sprintf( __( 'You may enter a maximum of %s characters.', 'jigoshop' ), $custom_length );
			
			echo '<div class="panel" id="tab-customize">';
			echo '<p>' . apply_filters('jigoshop_product_customize_heading', __('Enter your personal information as you want it to appear on the product.<br />', 'jigoshop').$length_str) . '</p>';

			?>

				<form action="" method="post">

					<input type="hidden" name="customized_id" value="<?php echo esc_attr( $_product->ID ); ?>" />

					<?php
					if ( $custom_length == '' ) :
					?>
						<textarea
							id="jigoshop_customized_product"
							name="jigoshop_customized_product"
							cols="60"
							rows="4"><?php echo esc_textarea( $custom ); ?></textarea>
					<?php else : ?>
						<input 
							type="text"
							id="jigoshop_customized_product"
							name="jigoshop_customized_product"
							size="<?php echo $custom_length; ?>"
							maxlength="<?php echo $custom_length; ?>"
							value="<?php echo esc_attr( $custom ); ?>" />
					<?php endif; ?>
					
					<p class="submit"><input name="Submit" type="submit" class="button-alt add_personalization" value="<?php _e( "Save Personalization", 'jigoshop' ); ?>" /></p>

				</form>

			<?php
			echo '</div>';
		endif;
	}
}


/**
 * Jigoshop Product Thumbnail
 **/
if (!function_exists('jigoshop_get_product_thumbnail')) {
	function jigoshop_get_product_thumbnail( $size = 'shop_small' ) {

		global $post;

		if ( has_post_thumbnail() )
			return get_the_post_thumbnail($post->ID, $size);
		else
			return jigoshop_get_image_placeholder( $size );
	}
}


/**
 * Jigoshop Product Category Image
 **/
if (!function_exists('jigoshop_product_cat_image')) {
	function jigoshop_product_cat_image($id) {

		if( empty($id) )
			return false;

		$thumbnail_id   = get_metadata('jigoshop_term', $id, 'thumbnail_id', true);
		$category_image = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id )
										: jigoshop::assets_url().'/assets/images/placeholder.png';

		return array('image' => $category_image, 'thumb_id' => $thumbnail_id);

	}
}


/**
 * Jigoshop Product Image Placeholder
 * @since 0.9.9
 **/
if (!function_exists('jigoshop_get_image_placeholder')) {
	function jigoshop_get_image_placeholder( $size = 'shop_small' ) {
		$image_size = jigoshop_get_image_size( $size );
		return '<img src="'.jigoshop::assets_url().'/assets/images/placeholder.png" alt="Placeholder" width="'.$image_size[0].'" height="'.$image_size[1].'" />';
	}
}

/**
 * Jigoshop Related Products
 **/
if (!function_exists('jigoshop_output_related_products')) {
	function jigoshop_output_related_products() {
        $jigoshop_options = Jigoshop_Base::get_options();
		if ($jigoshop_options->get_option ('jigoshop_enable_related_products') != 'no')
			// 2 Related Products in 2 columns
			jigoshop_related_products( 2, 2 );
	}
}

if (!function_exists('jigoshop_related_products')) {
	function jigoshop_related_products( $posts_per_page = 4, $post_columns = 4, $orderby = 'rand' ) {

		global $_product, $columns, $per_page;

		// Pass vars to loop
		$per_page = $posts_per_page;
		$columns = $post_columns;

		$related = $_product->get_related( $posts_per_page );
		if (sizeof($related)>0) :
			echo '<div class="related products"><h2>'.__('Related Products', 'jigoshop').'</h2>';
			$args = array(
				'post_type'	=> 'product',
				'ignore_sticky_posts'	=> 1,
				'posts_per_page' => $per_page,
				'orderby' => $orderby,
				'post__in' => $related
			);
			$args = apply_filters('jigoshop_related_products_args', $args);
			query_posts($args);
			jigoshop_get_template_part( 'loop', 'shop' );
			echo '</div>';
			wp_reset_query();
		endif;
		$per_page = null;   // reset for cross sells if enabled
		$columns = null;

	}
}

/**
 * Jigoshop Shipping Calculator
 **/
if (!function_exists('jigoshop_shipping_calculator')) {
	function jigoshop_shipping_calculator() {
		if (jigoshop_shipping::show_shipping_calculator()) :
		?>
		<form class="shipping_calculator" action="<?php echo esc_url( jigoshop_cart::get_cart_url() ); ?>" method="post">
			<h2><a href="#" class="shipping-calculator-button"><?php _e('Calculate Shipping', 'jigoshop'); ?> <span>&darr;</span></a></h2>
			<section class="shipping-calculator-form">
			<p class="form-row">
				<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state" rel="calc_shipping_state">
					<?php
						foreach(jigoshop_countries::get_allowed_countries() as $key=>$value) :
							echo '<option value="'.esc_attr($key).'"';
							if (jigoshop_customer::get_shipping_country()==$key) echo ' selected="selected"';
							echo '>'.$value.'</option>';
						endforeach;
					?>
				</select>
			</p>
			<div class="col2-set">
				<p class="form-row col-1">
					<?php
						$current_cc = jigoshop_customer::get_shipping_country();
						$current_r = jigoshop_customer::get_shipping_state();
						$states = jigoshop_countries::$states;

						if (jigoshop_countries::country_has_states($current_cc)) :
							// Dropdown
							?>
							<span>
								<select name="calc_shipping_state" id="calc_shipping_state"><option value=""><?php _e('Select a state&hellip;', 'jigoshop'); ?></option><?php
									foreach($states[$current_cc] as $key=>$value) :
										echo '<option value="'.esc_attr($key).'"';
										if ($current_r==$key) echo 'selected="selected"';
										echo '>'.$value.'</option>';
									endforeach;
								?></select>
							</span>
							<?php
						else :
							// Input
							?>
							<input type="text" class="input-text" value="<?php echo esc_attr( $current_r ); ?>" placeholder="<?php _e('state', 'jigoshop'); ?>" name="calc_shipping_state" id="calc_shipping_state" />
							<?php
						endif;
					?>
				</p>
				<p class="form-row col-2">
					<input type="text" class="input-text" value="<?php echo esc_attr( jigoshop_customer::get_shipping_postcode() ); ?>" placeholder="<?php _e('Postcode/Zip', 'jigoshop'); ?>" title="<?php _e('Postcode', 'jigoshop'); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" />
				</p>
				<?php do_action('jigoshop_after_shipping_calculator_fields');?>
			</div>
			<p><button type="submit" name="calc_shipping" value="1" class="button"><?php _e('Update Totals', 'jigoshop'); ?></button></p>
			<p>
			<?php
				$available_methods = jigoshop_shipping::get_available_shipping_methods();
				foreach ( $available_methods as $method ) :

                    for ($i = 0; $i < $method->get_rates_amount(); $i++) {
                    ?>
                        <div class="col2-set">
                            <p class="form-row col-1">

                                <?php
                                echo '<input type="radio" name="shipping_rates" value="' . esc_attr( $method->id . ':' . $i ) . '"' . ' class="shipping_select"';
                                if ( $method->get_cheapest_service() == $method->get_selected_service($i) && $method->is_chosen() ) echo ' checked>'; else echo '>';
								echo $method->get_selected_service($i);
                                ?>
                            <p class="form-row col-2"><?php
                            
								if ($method->get_selected_price($i) > 0) :
									echo jigoshop_price($method->get_selected_price($i));
									echo __(' (ex. tax)', 'jigoshop');
								else :
									echo __('Free', 'jigoshop');
								endif;
                                ?>
                        </div>
                    <?php
                    }
				endforeach;
			?>
			<input type="hidden" name="cart-url" value="<?php echo esc_attr( jigoshop_cart::get_cart_url() ); ?>">
			<?php jigoshop::nonce_field('cart') ?>
			</section>
		</form>
		<?php
		endif;
	}
}

/**
 * Jigoshop Login Form
 **/
if (!function_exists('jigoshop_login_form')) {
	function jigoshop_login_form() {

		if (is_user_logged_in()) return;

		?>
		<form method="post" class="login">
			<p class="form-row form-row-first">
				<label for="username"><?php _e('Username', 'jigoshop'); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="username" id="username" />
			</p>
			<p class="form-row form-row-last">
				<label for="password"><?php _e('Password', 'jigoshop'); ?> <span class="required">*</span></label>
				<input class="input-text" type="password" name="password" id="password" />
			</p>
			<div class="clear"></div>

			<p class="form-row">
				<?php jigoshop::nonce_field('login', 'login') ?>
				<input type="submit" class="button" name="login" value="<?php esc_html_e('Login', 'jigoshop'); ?>" />
				<a class="lost_password" href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>"><?php _e('Lost Password?', 'jigoshop'); ?></a>
			</p>
		</form>
		<?php
	}
}

/**
 * Jigoshop Login Form
 **/
if (!function_exists('jigoshop_checkout_login_form')) {
	function jigoshop_checkout_login_form() {

        $jigoshop_options = Jigoshop_Base::get_options();
		if (is_user_logged_in() || $jigoshop_options->get_option('jigoshop_enable_guest_login') != 'yes') return;

		?><p class="info"><?php _e('Already registered?', 'jigoshop'); ?> <a href="#" class="showlogin"><?php _e('Click here to login', 'jigoshop'); ?></a></p><?php

		jigoshop_login_form();
	}
}

/**
 * Jigoshop Verify Checkout States Message
 **/
if (!function_exists('jigoshop_verify_checkout_states_for_countries_message')) {
	function jigoshop_verify_checkout_states_for_countries_message() {
		if ( Jigoshop_Base::get_options()->get_option( 'jigoshop_verify_checkout_info_message' ) == 'yes' ) {
			// the following will return true or false if a country requires states
			if ( ! jigoshop_customer::has_valid_shipping_state() ) {
				echo '<div class="clear"></div><div class="payment_message">' . __('You may have already established your Billing and Shipping state, but please verify it is correctly set for your location as well as all the rest of your information before placing your Order.','jigoshop') . '</div>';
			} else {
				echo '<div class="clear"></div><div class="payment_message">' . __('Please verify that all your information is correctly entered before placing your Order.','jigoshop') . '</div>';
			}
		}
	}
}

/**
 * Jigoshop EU B2B VAT Message
 **/
if (!function_exists('jigoshop_eu_b2b_vat_message')) {
	function jigoshop_eu_b2b_vat_message() {
		if ( jigoshop_countries::is_eu_country( jigoshop_customer::get_country() )
		&& Jigoshop_Base::get_options()->get_option( 'jigoshop_eu_vat_reduction_message' ) == 'yes' ) {
			echo '<div class="clear"></div><div class="payment_message">' . __('If you have entered an EU VAT Number, it will be looked up when you <strong>Place</strong> your Order and verified.  At that time <strong><em>Only</em></strong>, will VAT then be removed from the final Order and totals adjusted.  You may enter your EU VAT Number either with, or without, the 2 character EU country code in front.','jigoshop') . '</div>';
		}
	}
}

/**
 * Jigoshop Breadcrumb
 **/
if (!function_exists('jigoshop_breadcrumb')) {
	function jigoshop_breadcrumb( $delimiter = ' &rsaquo; ', $wrap_before = '<div id="breadcrumb">', $wrap_after = '</div>', $before = '', $after = '', $home = null ) {

	 	global $post, $wp_query, $author, $paged;
        $jigoshop_options = Jigoshop_Base::get_options();

	 	if( !$home ) $home = _x('Home', 'breadcrumb', 'jigoshop');

	 	$home_link = home_url();

	 	$prepend = '';

	 	if ( $jigoshop_options->get_option('jigoshop_prepend_shop_page_to_urls')=="yes" && jigoshop_get_page_id('shop') && get_option('page_on_front') !== jigoshop_get_page_id('shop') )
	 		$prepend =  $before . '<a href="' . esc_url( jigoshop_cart::get_shop_url() ). '">' . get_the_title( jigoshop_get_page_id('shop') ) . '</a> ' . $after . $delimiter;


	 	if ( (!is_home() && !is_front_page() && !(is_post_type_archive() && get_option('page_on_front')==jigoshop_get_page_id('shop'))) || is_paged() ) :

			echo $wrap_before;

			echo $before  . '<a class="home" href="' . $home_link . '">' . $home . '</a> '  . $after . $delimiter ;

			if ( is_category() ) :

	      		$cat_obj = $wp_query->get_queried_object();
	      		$this_category = $cat_obj->term_id;
	      		$this_category = get_category( $this_category );
	      		if ($thisCat->parent != 0) :
	      			$parent_category = get_category( $this_category->parent );
	      			echo get_category_parents($parent_category, TRUE, $delimiter );
	      		endif;
	      		echo $before . single_cat_title('', false) . $after;

	 		elseif ( is_tax('product_cat') ) :

	 			//echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . ucwords($jigoshop_options->get_option('jigoshop_shop_slug')) . '</a>' . $after . $delimiter;

	 			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

				$parents = array();
				$parent = $term->parent;
				while ($parent):
					$parents[] = $parent;
					$new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
					$parent = $new_parent->parent;
				endwhile;
				if(!empty($parents)):
					$parents = array_reverse($parents);
					foreach ($parents as $parent):
						$item = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
						echo $before .  '<a href="' . get_term_link( $item->slug, 'product_cat' ) . '">' . $item->name . '</a>' . $after . $delimiter;
					endforeach;
				endif;

	 			$queried_object = $wp_query->get_queried_object();
	      		echo $prepend . $before . $queried_object->name . $after;

	      	elseif ( is_tax('product_tag') ) :

	 			$queried_object = $wp_query->get_queried_object();
	      		echo $prepend . $before . __('Products tagged &ldquo;', 'jigoshop') . $queried_object->name . '&rdquo;' . $after;

	 		elseif ( is_day() ) :

				echo $before . '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>' . $after . $delimiter;
				echo $before . '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a>' . $after . $delimiter;
				echo $before . get_the_time('d') . $after;

			elseif ( is_month() ) :

				echo $before . '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>' . $after . $delimiter;
				echo $before . get_the_time('F') . $after;

			elseif ( is_year() ) :

				echo $before . get_the_time('Y') . $after;

	 		elseif ( is_post_type_archive('product') && get_option('page_on_front') !== jigoshop_get_page_id('shop') ) :

	 			$_name = jigoshop_get_page_id('shop') ? get_the_title( jigoshop_get_page_id('shop') ) : ucwords($jigoshop_options->get_option('jigoshop_shop_slug'));

	 			if (is_search()) :

	 				echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . $_name . '</a>' . $delimiter . __('Search results for &ldquo;', 'jigoshop') . get_search_query() . '&rdquo;' . $after;

	 			else :

	 				echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . $_name . '</a>' . $after;

	 			endif;

			elseif ( is_single() && !is_attachment() ) :

				if ( get_post_type() == 'product' ) :

	       			//echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . ucwords($jigoshop_options->get_option('jigoshop_shop_slug')) . '</a>' . $after . $delimiter;
	       			echo $prepend;

	       			if ($terms = get_the_terms( $post->ID, 'product_cat' )) :
						$term = apply_filters( 'jigoshop_product_cat_breadcrumb_terms', current($terms), $terms);
						$parents = array();
						$parent = $term->parent;
						while ($parent):
							$parents[] = $parent;
							$new_parent = get_term_by( 'id', $parent, 'product_cat');
							$parent = $new_parent->parent;
						endwhile;
						if(!empty($parents)):
							$parents = array_reverse($parents);
							foreach ($parents as $parent):
								$item = get_term_by( 'id', $parent, 'product_cat');
								echo $before . '<a href="' . get_term_link( $item->slug, 'product_cat' ) . '">' . $item->name . '</a>' . $after . $delimiter;
							endforeach;
						endif;
						echo $before . '<a href="' . get_term_link( $term->slug, 'product_cat' ) . '">' . $term->name . '</a>' . $after . $delimiter;
					endif;

	        		echo $before . get_the_title() . $after;

				elseif ( get_post_type() != 'post' ) :
					$post_type = get_post_type_object(get_post_type());
	        		$slug = $post_type->rewrite;
	       			echo $before . '<a href="' . get_post_type_archive_link(get_post_type()) . '">' . $post_type->labels->singular_name . '</a>' . $after . $delimiter;
	        		echo $before . get_the_title() . $after;
				else :
					$cat = current(get_the_category());
					echo get_category_parents($cat, TRUE, $delimiter);
					echo $before . get_the_title() . $after;
				endif;

	 		elseif ( is_404() ) :

		    	echo $before . __('Error 404', 'jigoshop') . $after;

	    	elseif ( !is_single() && !is_page() && get_post_type() != 'post' ) :

				$post_type = get_post_type_object(get_post_type());
				if ($post_type) : echo $before . $post_type->labels->singular_name . $after; endif;

			elseif ( is_attachment() ) :

				$parent = get_post($post->post_parent);
				$cat = get_the_category($parent->ID); $cat = $cat[0];
				echo get_category_parents($cat, TRUE, '' . $delimiter);
				echo $before . '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>' . $after . $delimiter;
				echo $before . get_the_title() . $after;

			elseif ( is_page() && !$post->post_parent ) :

				echo $before . get_the_title() . $after;

			elseif ( is_page() && $post->post_parent ) :

				$parent_id  = $post->post_parent;
				$breadcrumbs = array();
				while ($parent_id) {
					$page = get_page($parent_id);
					$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
					$parent_id  = $page->post_parent;
				}
				$breadcrumbs = array_reverse($breadcrumbs);
				foreach ($breadcrumbs as $crumb) :
					echo $crumb . '' . $delimiter;
				endforeach;
				echo $before . get_the_title() . $after;

			elseif ( is_search() ) :

				echo $before . __('Search results for &ldquo;', 'jigoshop') . get_search_query() . '&rdquo;' . $after;

			elseif ( is_tag() ) :

	      		echo $before . __('Posts tagged &ldquo;', 'jigoshop') . single_tag_title('', false) . '&rdquo;' . $after;

			elseif ( is_author() ) :

				$userdata = get_userdata($author);
				echo $before . __('Author: ', 'jigoshop') . $userdata->display_name . $after;

		    endif;

			if ( get_query_var('paged') ) :

				echo ' (' . __('Page', 'jigoshop') . ' ' . get_query_var('paged') .')';

			endif;

	    	echo $wrap_after;

		endif;

	}
}

/**
 * Hook to remove the 'singular' class, for the twenty eleven theme, to properly display the sidebar
 *
 * @param array $classes
 */
function jigoshop_body_classes ($classes) {

	if( ! is_content_wrapped() ) return $classes;

	$key = array_search('singular', $classes);
	if ( $key !== false ) unset($classes[$key]);
	return $classes;

}

/**
 * Order review table for checkout
 **/
function jigoshop_order_review() {
	jigoshop_get_template('checkout/review_order.php', false);
}

