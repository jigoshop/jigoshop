<?php
/**
 * Layered Navigation Widget
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Widgets
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Widget_Layered_Nav extends WP_Widget {

	/**
	 * Constructor
	 *
	 * Setup the widget with the available options
	 */
	public function __construct() {
		$options = array(
			'description' => __( "Shows a custom attribute in a widget which lets you narrow down the list of shown products in categories.", 'jigoshop'),
		);

		// Create the widget
		parent::__construct('layered_nav', __('Jigoshop: Layered Nav', 'jigoshop'), $options);
	}

	/**
	 * Widget
	 *
	 * Display the widget in the sidebar
	 *
	 * @param	array	sidebar arguments
	 * @param	array	instance
	 */
	public function widget( $args, $instance ) {

		// TODO: Optimize this code

		// Extract the widget arguments
		extract($args);
		global $_chosen_attributes, $wpdb, $jigoshop_all_post_ids_in_view;

		// Hide widget if not product related
		if ( ! is_product_list() )
			return false;

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			( $instance['title'] ) ? $instance['title'] : __( 'Filter by Attributes', 'jigoshop' ),
			$instance,
			$this->id_base
		);

		// Check if taxonomy exists
		$taxonomy = 'pa_'.sanitize_title($instance['attribute']);
		if ( ! taxonomy_exists($taxonomy) )
			return false;

		// Get all the terms that aren't empty
		$args = array(
			'hide_empty' => true,
		);
		$terms = get_terms( $taxonomy, $args );
		$has_terms = (bool) $terms;

		// If has terms print layered navigation
		if($has_terms) {

			$found = false;
			ob_start();

			// Print the widget wrapper & title
			echo $before_widget;
			echo $before_title . $title . $after_title;

			//Remove param link
			$remove_link = remove_query_arg('filter_'.sanitize_title($instance['attribute']));
			echo "<a class=\"layerd_nav_clear\" href=\"{$remove_link}\">Clear</a>";

			// Open the list
			echo "<ul>";

			foreach ($terms as $term) {

				$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

				// Get product count & set flag
				$count = sizeof(array_intersect($_products_in_term, $jigoshop_all_post_ids_in_view ));
				$has_products = (bool) $count;

				if ($has_products) $found = true;

				$class = '';

				$arg = 'filter_'.sanitize_title($instance['attribute']);

				if (isset($_GET[ $arg ])) $current_filter = explode(',', $_GET[ $arg ]); else $current_filter = array();

				if (!is_array($current_filter)) $current_filter = array();

				if (!in_array($term->term_id, $current_filter)) $current_filter[] = $term->term_id;

				// Base Link decided by current page
				if (defined('SHOP_IS_ON_FRONT')) :
					$link = '';
				elseif ( is_shop() ) :
					$link = get_post_type_archive_link('product');
				else :
					$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
				endif;

				// All current filters
				if ($_chosen_attributes) foreach ($_chosen_attributes as $name => $value) :
					if ($name!==$taxonomy) :
						$link = add_query_arg( sanitize_title(str_replace('pa_', 'filter_', $name)), implode(',', $value), $link );
					endif;
				endforeach;

				// Min/Max
				if (isset($_GET['min_price'])) :
					$link = add_query_arg( 'min_price', $_GET['min_price'], $link );
				endif;
				if (isset($_GET['max_price'])) :
					$link = add_query_arg( 'max_price', $_GET['max_price'], $link );
				endif;

				// Current Filter = this widget
				if (isset( $_chosen_attributes[$taxonomy] ) && is_array($_chosen_attributes[$taxonomy]) && in_array($term->term_id, $_chosen_attributes[$taxonomy])) :
					$class = 'class="chosen"';
				else :
					$link = add_query_arg( $arg, implode(',', $current_filter), $link );
				endif;

				// Search Arg
				if (get_search_query()) :
					$link = add_query_arg( 's', get_search_query(), $link );
				endif;

				// Post Type Arg
				if (isset($_GET['post_type'])) :
					$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
				endif;

				echo '<li '.$class.'>';

				if ($has_products) echo '<a href="'.esc_url($link).'">'; else echo '<span>';

				echo $term->name;

				if ($has_products) echo '</a>'; else echo '</span>';

				echo ' <small class="count">'.$count.'</small></li>';

			}

			echo "</ul>"; // Close the list

			// Print closing widget wrapper
			echo $after_widget;

			if ( ! $found ) {
				ob_clean(); // clear the buffer
				return false; // display nothing
			} else {
				echo ob_get_clean(); // output the buffer
			}

		}
	}

	/**
	 * Update
	 *
	 * Handles the processing of information entered in the wordpress admin
	 *
	 * @param	array	new instance
	 * @param	array	old instance
	 * @return	array	instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['attribute'] = stripslashes($new_instance['attribute']);

		return $instance;
	}

	/**
	 * Form
	 *
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	public function form( $instance ) {
		global $wpdb;

		// Get values from instance
		$title = (isset($instance['title'])) ? esc_attr($instance['title']) : null;
		$attr_tax = jigoshop_product::getAttributeTaxonomies();

		// Widget title
		echo '<p>';
		echo '<label for="' . esc_attr( $this->get_field_id('title')  ) . '"> ' . _e('Title:', 'jigoshop') . '</label>';
		echo '<input type="text" class="widefat" id="' . esc_attr( $this->get_field_id('title')  ) . '" name="' . esc_attr( $this->get_field_name('title')  ) . '" value="' . esc_attr( $title ) . '" />';
		echo '</p>';

		// Print attribute selector
		if ( ! empty($attr_tax) ) {
			echo '<p>';
			echo '<label for="' . esc_attr( $this->get_field_id('attribute')  ) . '">' . __('Attribute:', 'jigoshop') . '</label> ';
			echo '<select id="' . esc_attr( $this->get_field_id('attribute')  ) . '" name="' . esc_attr( $this->get_field_name('attribute')  ) . '">';
			foreach($attr_tax as $tax) {

				if (taxonomy_exists('pa_'.sanitize_title($tax->attribute_name))) {
					echo '<option value="' . esc_attr( $tax->attribute_name  ) . '" ' . (isset($instance['attribute']) && $instance['attribute'] == $tax->attribute_name ? 'selected' : null) . '>';
					echo $tax->attribute_name;
					echo '</option>';
				}
			}

			echo '</select>';
			echo '</p>';
		}
	}
} // class Jigoshop_Widget_Layered_Nav

function jigoshop_layered_nav_query( $filtered_posts ) {

	global $_chosen_attributes;

	if (sizeof($_chosen_attributes)>0) :

		$matched_products = array();
		$filtered = false;

		foreach ($_chosen_attributes as $attribute => $values) :
			if (sizeof($values)>0) :
				foreach ($values as $value) :

					$posts = get_objects_in_term( $value, $attribute );
					if (!is_wp_error($posts) && (sizeof($matched_products)>0 || $filtered)) :
						$matched_products = array_intersect($posts, $matched_products);
					elseif (!is_wp_error($posts)) :
						$matched_products = $posts;
					endif;

					$filtered = true;

				endforeach;
			endif;
		endforeach;

		if ($filtered) :
			$matched_products[] = 0;
			$filtered_posts = array_intersect($filtered_posts, $matched_products);
		endif;

	endif;

	return $filtered_posts;
}
add_filter( 'loop-shop-posts-in', 'jigoshop_layered_nav_query' );

function jigoshop_layered_nav_init() {

	global $_chosen_attributes;

	$_chosen_attributes = array();

	$attribute_taxonomies = jigoshop_product::getAttributeTaxonomies();
	if ( $attribute_taxonomies ) :
		foreach ($attribute_taxonomies as $tax) :

	    	$attribute = sanitize_title($tax->attribute_name);
	    	$taxonomy = 'pa_' . $attribute;
	    	$name = 'filter_' . $attribute;

	    	if (isset($_GET[$name]) && taxonomy_exists($taxonomy)) $_chosen_attributes[$taxonomy] = explode(',', $_GET[$name] );

	    endforeach;
    endif;

}
add_action( 'init', 'jigoshop_layered_nav_init', 1 );
