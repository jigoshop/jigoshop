<?php
/**
 * FUNCTIONS USED IN TEMPLATE FILES
 **/

/**
 * Jigoshop Product Thumbnail
 **/
if (!function_exists('jigoshop_login_form')) {
	function jigoshop_get_product_thumbnail( $size = 'shop_small', $placeholder_width = 0, $placeholder_height = 0 ) {
		
		global $post;
		
		if (!$placeholder_width) $placeholder_width = jigoshop::get_var('shop_small_w');
		if (!$placeholder_height) $placeholder_height = jigoshop::get_var('shop_small_h');
		
		if ( has_post_thumbnail() ) return get_the_post_thumbnail($post->ID, $size); else return '<img src="'.jigoshop::plugin_url(). '/assets/images/placeholder.png" alt="Placeholder" width="'.$placeholder_width.'" height="'.$placeholder_height.'" />';
		
	}
}

/**
 * Jigoshop Shipping Calculator
 **/
if (!function_exists('jigoshop_shipping_calculator')) {
	function jigoshop_shipping_calculator() {
		if (jigoshop_shipping::$enabled && get_option('jigoshop_enable_shipping_calc')=='yes' && jigoshop_cart::needs_shipping()) : 
		?>
		<form class="shipping_calculator" action="<?php echo jigoshop_cart::get_cart_url(); ?>" method="post">
			<h2><a href="#" class="shipping-calculator-button"><?php _e('Calculate Shipping', 'jigoshop'); ?> <span>&darr;</span></a></h2>
			<section class="shipping-calculator-form">
			<p class="form-row">
				<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state" rel="calc_shipping_state">
					<option value=""><?php _e('Select a country&hellip;', 'jigoshop'); ?></option>
					<?php				
						foreach(jigoshop_countries::get_allowed_countries() as $key=>$value) :
							echo '<option value="'.$key.'"';
							if (jigoshop_customer::get_shipping_country()==$key) echo 'selected="selected"';
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
						
						if (isset( $states[$current_cc][$current_r] )) :
							// Dropdown
							?>
							<span>
								<select name="calc_shipping_state" id="calc_shipping_state"><option value=""><?php _e('Select a state&hellip;', 'jigoshop'); ?></option><?php
									foreach($states[$current_cc] as $key=>$value) :
										echo '<option value="'.$key.'"';
										if ($current_r==$key) echo 'selected="selected"';
										echo '>'.$value.'</option>';
									endforeach;
								?></select>
							</span>
							<?php
						else :
							// Input
							?>
							<span class="input-text">
								<input type="text" value="<?php echo $current_r; ?>" placeholder="<?php _e('state', 'jigoshop'); ?>" name="calc_shipping_state" id="calc_shipping_state" />
							</span>
							<?php
						endif;
					?>
				</p>
				<p class="form-row col-2">
					<span class="input-text"><input type="text" value="<?php echo jigoshop_customer::get_shipping_postcode(); ?>" placeholder="<?php _e('Postcode/Zip', 'jigoshop'); ?>" title="<?php _e('Postcode', 'jigoshop'); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" /></span>
				</p>
			</div>
			<p><button type="submit" name="calc_shipping" value="1" class="button"><?php _e('Update Totals', 'jigoshop'); ?></button></p>
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
		?>
		<form method="post" class="login">
			<p class="form-row form-row-first">
				<label for="username"><?php _e('Username', 'jigoshop'); ?> <span class="required">*</span></label>
				<span class="input-text"><input type="text" name="username" id="username" /></span>
			</p>
			<p class="form-row form-row-last">
				<label for="password"><?php _e('Password', 'jigoshop'); ?> <span class="required">*</span></label>
				<span class="input-text"><input type="password" name="password" id="password" /></span>
			</p>
			<div class="clear"></div>
			
			<p class="form-row">
				<?php jigoshop::nonce_field('login', 'login') ?>
				<input type="submit" class="button" name="login" value="<?php _e('Login', 'jigoshop'); ?>" />
				<a class="lost_password" href="<?php echo home_url('wp-login.php?action=lostpassword'); ?>"><?php _e('Lost Password?', 'jigoshop'); ?></a>
			</p>
		</form>
		<?php
	}
}

/**
 * Jigoshop Breadcrumb
 **/
if (!function_exists('jigoshop_breadcrumb')) {
	function jigoshop_breadcrumb( $delimiter = ' &rsaquo; ', $wrap_before = '<div id="breadcrumb">', $wrap_after = '</div>', $before = '', $after = '', $home = null ) {
	 	
	 	global $post, $wp_query, $author;
	 	
	 	if( !$home ) $home = _x('Home', 'breadcrumb', 'jigoshop'); 	
	 	
	 	$home_link = home_url();
	 	
	 	$prepend = '';
	 	
	 	if ( get_option('jigoshop_prepend_shop_page_to_urls')=="yes" && get_option('jigoshop_shop_page_id') )
	 		$prepend =  $before . '<a href="' . get_permalink( get_option('jigoshop_shop_page_id') ) . '">' . get_the_title( get_option('jigoshop_shop_page_id') ) . '</a> ' . $after . $delimiter;
	 	
		if ( !is_home() && !is_front_page() || is_paged() ) :
	 
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
	 			
	 			//echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . ucwords(get_option('jigoshop_shop_slug')) . '</a>' . $after . $delimiter;
	 			
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
	 		
	 		elseif ( is_post_type_archive('product') ) :
	
	 			$_name = get_option('jigoshop_shop_page_id') ? get_the_title( get_option('jigoshop_shop_page_id') ) : ucwords(get_option('jigoshop_shop_slug'));
	 		
	 			if (is_search()) :
	 				
	 				echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . $_name . '</a>' . $delimiter . __('Search results for &ldquo;', 'jigoshop') . get_search_query() . '&rdquo;' . $after;
	 			
	 			else :
	 			
	 				echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . $_name . '</a>' . $after;
	 			
	 			endif;
	 		
			elseif ( is_single() && !is_attachment() ) :
				
				if ( get_post_type() == 'product' ) :
					
	       			//echo $before . '<a href="' . get_post_type_archive_link('product') . '">' . ucwords(get_option('jigoshop_shop_slug')) . '</a>' . $after . $delimiter;
	       			echo $prepend;
	       			
	       			if ($terms = wp_get_object_terms( $post->ID, 'product_cat' )) :
						$term = current($terms);
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