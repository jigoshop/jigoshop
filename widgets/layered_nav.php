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
 * @package    Jigoshop
 * @category   Widgets
 * @author     Jigowatt
 * @since	   1.0
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
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
		global $_chosen_attributes, $wpdb, $all_post_ids;
		
		// Hide widget if not product related
		if ( ! is_product_list() )
			return false;

		// Set the widget title
		$title = ($instance['title']) ? $instance['title'] : apply_filters('widget_title', '', $instance, $this->id_base);
		
		// Check if taxonomy exists
		$taxonomy = 'pa_'.strtolower(sanitize_title($instance['attribute']));
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
			
			// Open the list
			echo "<ul>";

			// Reduce count based on chosen attributes
			$all_post_ids = jigoshop_layered_nav_query( $all_post_ids );
			$all_post_ids = jigoshop_price_filter( $all_post_ids );

			foreach ($terms as $term) {
			
				$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );
				
				// Get product count & set flag
				$count = sizeof(array_intersect($_products_in_term, $all_post_ids));
				$has_products = (bool) $count;
				
				if ($has_products) $found = true;
				
				$class = '';
				
				$arg = 'filter_'.strtolower(sanitize_title($instance['attribute']));
				
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
						$link = add_query_arg( strtolower(sanitize_title(str_replace('pa_', 'filter_', $name))), implode(',', $value), $link );
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
				
				if ($has_products) echo '<a href="'.$link.'">'; else echo '<span>';
				
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
		echo '<label for="' . $this->get_field_id('title') . '"> ' . _e('Title:', 'jigoshop') . '</label>';
		echo '<input type="text" class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . $title . '" />';
		echo '</p>';
		
		// Print attribute selector
		if ( ! empty($attr_tax) ) {
			echo '<p>';
			echo '<label for="' . $this->get_field_id('attribute') . '">' . __('Attribute:', 'jigoshop') . '</label> ';
			echo '<select id="' . $this->get_field_id('attribute') . '" name="' . $this->get_field_name('attribute') . '">';
			foreach($attr_tax as $tax) {
				
				if (taxonomy_exists('pa_'.strtolower(sanitize_title($tax->attribute_name)))) {
					echo '<option value="' . $tax->attribute_name . '" ' . (isset($instance['attribute']) && $instance['attribute'] == $tax->attribute_name ? 'selected' : null) . '>';
					echo $tax->attribute_name;
					echo '</option>';
				}
			}
			
			echo '</select>';
			echo '</p>';
		}
	}
} // class Jigoshop_Widget_Layered_Nav