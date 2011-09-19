<?php
/**
 * Tag Cloud Widget
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
 
class Jigoshop_Widget_Tag_Cloud extends WP_Widget {

	/**
	 * Constructor
	 * 
	 * Setup the widget with the available options
	 */
	public function __construct() {
	
		$options = array(
			'description' => __( "Your most used product tags in cloud format", 'jigoshop'),
		);
		
		// Create the widget
		parent::__construct('product_tag_cloud', __('Jigoshop: Product Tag Cloud', 'jigoshop'), $options);
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
		
		// Set the widget title
		$title = ( ! empty($instance['title']) ) ? $instance['title'] : __('Product Tags', 'jigoshop');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);

		// Print the widget wrapper & title
		echo $before_widget;
		echo $before_title . $title . $after_title;

		// Print tag cloud with wrapper		
		echo '<div class="tagcloud">';
		wp_tag_cloud( apply_filters('widget_tag_cloud_args', array('taxonomy' => 'product_tag') ) );
		echo "</div>\n";
		
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
		
		// Save new values
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['taxonomy'] = stripslashes($new_instance['taxonomy']);
		
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
		$title = (isset($instance['title'])) ? esc_attr($instance['title']) : null;
		
		// Widget title
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'jigoshop') . '</label>';
		echo '<input type="text" class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . $title .'" />';
		echo '</p>';
	}
	
} // class Jigoshop_Widget_Tag_Cloud