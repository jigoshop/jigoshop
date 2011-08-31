<?php defined('ABSPATH') or die('No direct script access.');
/**
 * Price Filter Widget
 * 
 * Generates a range slider to filter products by price
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
class Jigoshop_Widget_Price_Filter extends WP_Widget {

	/**
	 * Constructor
	 * 
	 * Setup the widget with the available options
	 */
	public function __construct() {

		$options = array(
			'description' => __( "Shows a price filter slider in a widget which lets you narrow down the list of shown products in categories.", 'jigoshop'),
		);
		
		// Create the widget
		parent::__construct('price_filter', __('Price Filter', 'jigoshop'), $options);
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
	
		// Extract the widget arguments
		extract($args);
		global $_chosen_attributes, $wpdb, $all_post_ids;
		
		// Hide widget if is not a product
		if ( ! is_tax('product_cat') AND ! is_post_type_archive('product') AND ! is_tax('product_tag') ) {
			return false;
		}

		// Set the widget title
		$title = ($instance['title']) ? $instance['title'] : __('Filter by price', 'jigoshop');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		// Print the widget wrapper & title
		echo $before_widget;
		echo $before_title . $title . $after_title;
		
		// Remember current filters/search
		$fields = '';
		
		// If there is a search query save into hidden field
		if (get_search_query()) {
			$fields = '<input type="hidden" name="s" value="'.get_search_query().'" />';
		}
		
		// If there is a post_type save into a hidden field
		if (isset($_GET['post_type'])) {
			$fields .= '<input type="hidden" name="post_type" value="'.$_GET['post_type'].'" />';
		}
		
		// Save each chosen attribute in a hidden field
		if ($_chosen_attributes) {
			foreach ($_chosen_attributes as $key => $value) {
				$fields .= '<input type="hidden" name="'.str_replace('product_attribute_', 'filter_', $key).'" value="'.implode(',', $value).'" />';
			}
		}
		
		// Set mimium price
		// TODO: Find minimum price instead
		$min = 0;
		
		// TODO: Optimize this query
		$max = ceil($wpdb->get_var("SELECT max(meta_value + 0) 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE meta_key = 'price' AND (
			$wpdb->posts.ID IN (".implode(',', $all_post_ids).") 
			OR (
				$wpdb->posts.post_parent IN (".implode(',', $all_post_ids).")
				AND $wpdb->posts.post_parent != 0
			)
		)"));
		
		// Print the form
		echo '<form method="get" action="'. $_SERVER['PHP_SELF'] . '">
			<div class="price_slider_wrapper">
				<div class="price_slider"></div>
				<div class="price_slider_amount">
					<button type="submit" class="button">'.__('Filter', 'jigoshop').'</button>'.__('Price: ', 'jigoshop').'<span></span>
					<input type="hidden" id="max_price" name="max_price" value="'.$max.'" />
					<input type="hidden" id="min_price" name="min_price" value="'.$min.'" />
					'.$fields.'
				</div>
			</div>
		</form>';
		
		// Print closing widget wrapper
		echo $after_widget;
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
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
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
		// Get values from instance
		$title = (isset($instance['title'])) ? esc_attr($instance['title']) : null;
		
		// Widget title
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '"> ' . _e('Title:', 'jigoshop') . '</label>';
		echo '<input type="text" class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . $title . '" />';
		echo '</p>';
	}
} // class Jigoshop_Widget_Price_Filter