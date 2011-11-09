<?php
/**
 * Featured Products Widget
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

class Jigoshop_Widget_Featured_Products extends WP_Widget {

	/**
	 * Constructor
	 * 
	 * Setup the widget with the available options
	 */
	public function __construct() {
	
		$options = array(
			'classname' => 'widget_featured_products',
			'description' => __( "Featured products on your site", 'jigoshop')
		);
		
		// Create the widget
		parent::__construct('featured-products', __('Jigoshop: Featured Products', 'jigoshop'), $options);

		// Flush cache after every save
		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	/**
	 * Widget
	 * 
	 * Display the widget in the sidebar
	 *
	 * @param	array	sidebar arguments
	 * @param	array	instance
	 */
	function widget($args, $instance) {
		
		// Get the featured products from the cache
		$cache = wp_cache_get('widget_featured_products', 'widget');
		
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
		$title = (isset($instance['title'])) ? $instance['title'] : __('Featured Products', 'jigoshop');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		// Set number of products to fetch
		$number = isset($instance['number']) ? $instance['number'] : 0;
		
		// Set up query
		$query_args = array(
			'showposts'		=> $number,
			'post_type'		=> 'product',
			'post_status'	=> 'publish',
			'orderby'		=> get_option('jigoshop_catalog_sort_orderby'),
			'order'			=> get_option('jigoshop_catalog_sort_direction'),
			'meta_key'		=> 'featured',
			'meta_value'	=> 'yes',
    		'meta_query'	=> array(
    			array(
    				'key'		=> 'visibility',
    				'value'		=> array('catalog', 'visible'),
    				'compare'	=> 'IN',
    			),
    		)
		);
		
		// Run the query
		$q = new WP_Query($query_args);
		
		if ( $q->have_posts() ) {
			
			// Print the widget wrapper & title
			echo $before_widget;
			echo $before_title . ($title ? $title : __('Featured Products')) . $after_title; 
			
			// Open the list
			echo '<ul class="product_list_widget">';
			
			// Print each product
			while($q->have_posts()) : $q->the_post();  
				
				// Get the product instance
				$_product = new jigoshop_product(get_the_ID());
				
				echo '<li>';
					// Print the product image & title with a link to the permalink
					echo '<a href="'.get_permalink().'" title="'.esc_attr(get_the_title()).'">';
					echo (has_post_thumbnail()) ? the_post_thumbnail('shop_tiny') : jigoshop_get_image_placeholder('shop_tiny');
					echo '<span class="js_widget_product_title">' . get_the_title() . '</span>';
					echo '</a>';

					// Print the price with html wrappers
					echo '<span class="js_widget_product_price">' . $_product->get_price_html() . '</span>';
				echo '</li>';
			endwhile;
			
			echo '</ul>'; // Close the list
			
			// Print closing widget wrapper
			echo $after_widget;
			
			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();
		}
		
		// Flush output buffer and save to cache
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_featured_products', $cache, 'widget');
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
		$instance['number'] = (int) $new_instance['number'];
		
		// Flush the cache
		$this->flush_widget_cache();
		
		// Remove the cache entry from the options array
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_featured_products']) ) {
			delete_option('widget_featured_products');
		}

		return $instance;
	}

	/**
	 * Flush Widget Cache
	 * 
	 * Flushes the cached output
	 */
	public function flush_widget_cache() {
		wp_cache_delete('widget_featured_products', 'widget');
	}

	/**
	 * Form
	 * 
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;
		
		$number = apply_filters('jigoshop_widget_featured_default_number', 5, $instance, $this->id_base);
		$number = isset($instance['number']) ? abs($instance['number']) : $number;
		
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
} // class Jigoshop_Widget_Featured_Products