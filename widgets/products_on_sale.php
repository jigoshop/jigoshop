<?php
/**
 * Widget which lists products on sale
 *
 * Displays shopping cart widget
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
 class Jigoshop_Widget_Products_On_Sale extends WP_Widget {

 	/**
     * Constructor
     * 
     * Setup the widget with the available options
     */
    public function __construct() {
    	
        $options = array(
        	'classname'		=> 'widget_products_on_sale',
			'description'	=> __( "Display a list of products currently onsale", 'jigoshop')
        );
        
        // Create the widget
        parent::__construct('products-onsale', __('Jigoshop: Products On-Sale', 'jigoshop'), $options);

        // Flush cache after every save
		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }

    /**
	 * Widget
	 * 
	 * Display the widget in the sidebar
	 * Save output to the cache if empty
	 *
	 * @param	array	sidebar arguments
	 * @param	array	instance
	 */
	public function widget($args, $instance) {

		// Get the most recent products from the cache
		$cache = wp_cache_get('widget_recent_products', 'widget');

		// If no entry exists use array
		if ( ! is_array($cache) ) {
			$cache = array();
		}

		// If cached get from the cache
		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return false;
		}

		// Start buffering
		ob_start();
		extract($args);

		// Set the widget title
		$title = apply_filters('widget_title', 
			($instance['title']) ? $instance['title'] : __('Special Offers', 'jigoshop'), 
			$instance, $this->id_base);

		// Set number of products to fetch
		if( ! $number = abs($instance['number']) ) {
			$number = apply_filters('jigoshop_widget_recent_default_number', 10, $instance, $this->id_base);
		}
	}

    /**
	 * Update
	 * 
	 * Handles the processing of information entered in the wordpress admin
	 * Flushes the cache & removes entry from options array
	 *
	 * @param	array	new instance
	 * @param	array	old instance
	 * @return	array	instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		// Save the new values
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = abs($new_instance['number']);

		// Flush the cache
		$this->flush_widget_cache();

		// Remove the cache entry from the options array
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_products']) ) {
			delete_option('widget_recent_products');
		}

		return $instance;
	}

    /**
	 * Flush Widget Cache
	 * 
	 * Flushes the cached output
	 */
	public function flush_widget_cache() {
		wp_cache_delete('widget_recent_products', 'widget');
	}

	/**
	 * Form
	 * 
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	public function form( $instance ) {

		// Get instance data
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;
		$number = isset($instance['number']) ? abs($instance['number']) : 5;

		// Widget Title
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">' . _e('Title:', 'jigoshop') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="'. $title .'" />';
		echo '</p>';

		// Number of posts to fetch
		echo '<p>';
		echo '<label for="' . $this->get_field_id('number') . '">' . _e('Number of products to show:', 'jigoshop') . '</label>';
		echo '<input id="' . $this->get_field_id('number') . '" name="' . $this->get_field_name('number') . '" type="text" value="' . $number . '" size="3" />';
		echo '</p>';
	}
 }