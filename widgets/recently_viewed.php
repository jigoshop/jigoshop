<?php
/**
 * Recent Products Widget
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
 
 class Jigoshop_Widget_Recently_Viewed_Products extends WP_Widget {

 	public $instance;

	public function __construct() {
		$options = array(
			'classname'		=> 'widget_recently_viewed_products',
			'description'	=> __( "A list of your customers most recently viewed products", 'jigoshop')
		);
		
		// Create the widget
		parent::__construct('recently_viewed_products', __('Jigoshop: Recently Viewed'), $options);
		
		// Flush cache after every save
		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );

		// Attach the tracker to the product view action
		add_action( 'jigoshop_before_single_product', array(&$this, 'jigoshop_product_view_tracker'), 10, 2);
	}

	public function widget($args, $instance) {

		// Get the most recently viewed products from the cache
		//$cache = wp_cache_get('widget_recently_viewed_products', 'widget');
		$cache = null;

		// If no entry exists use array
		if ( ! is_array($cache) ) {
			$cache = array();
		}

		// If cached get from the cache
		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return false;
		}
		
		// Check if session contains recently viewed products
		if ( ! isset($_SESSION['recently_viewed_products']) OR ! sizeof($_SESSION['recently_viewed_products']) ) {
			return false;
		}

		// Start buffering the output
		ob_start();
		extract($args);

		// Set the widget title
		$title = apply_filters('widget_title', 
			($instance['title']) ? $instance['title'] : __('Recently Viewed Products', 'jigoshop'), 
			$instance, $this->id_base);

		// Set number of products to fetch
		if( ! $number = abs($instance['number']) ) {
			$number = apply_filters('jigoshop_widget_recent_default_number', 10, $instance, $this->id_base);
		}

		// Set up query
		$query_args = array(
			'showposts'		=> $number,
			'post_type'		=> 'product',
			'post_status'	=> 'publish',
			'nopaging'		=> true, // give me the gravy!
			'post__in'		=> $_SESSION['recently_viewed_products'],
			'orderby'		=> 'date', // TODO: Not ideal as it doesn't order latest first
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

		if($q->have_posts()) {
				
			// Print the widget wrapper & title
			echo $before_widget;
			echo $before_title . $title . $after_title;
			
			// Open the list
			echo '<ul class="product_list_widget recently_viewed_products">';
			
			// Print out each produt
			while($q->have_posts()) : $q->the_post();
			
				// Get new jigoshop_product instance
				$_product = new jigoshop_product(get_the_ID());
			 	
			 	echo '<li>';
			 		
			 		//print the product image & title with a permalink
			 		echo '<a href="'.get_permalink().'" title="'.esc_attr(get_the_title()).'">';
					echo (has_post_thumbnail()) ? the_post_thumbnail('shop_tiny') : jigoshop_get_image_placeholder('shop_tiny');
					echo '<span class="js_widget_product_title">' . get_the_title() . '</span>';
					echo '</a>';

					// Print the price with wrappers ..yum!
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
		wp_cache_set('widget_recent_products', $cache, 'widget');
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		// Save the new values
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = abs($new_instance['number']);
		$instance['show_variations'] = (bool) $new_instance['show_variations'];

		// Flush the cache
		$this->flush_widget_cache();

		// Unset the session array
		unset($_SESSION['recently_viewed_products']);

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
	 * Logs viewed products into the session
	 *
	 * @return void
	 **/
	public function jigoshop_product_view_tracker( $post, $_product ) {

		$instance = get_option('widget_recently_viewed_products');
		if( ! $number = $instance[2]['number']) { // is this always 2?
			return false; // stop the show!
		}

		// TODO this isn't the most efficient way... if it fails we should stop right?

		// Check if we already have some data
		if( ! is_array($_SESSION['recently_viewed_products']) ) {
				$_SESSION['recently_viewed_products'] = array();
		}

		// If the product isn't in the list, add it
		if( ! in_array($post->ID, $_SESSION['recently_viewed_products']) ) {
			$_SESSION['recently_viewed_products'][] = $post->ID;
		}

		// TODO: Figure out a way of getting the $number from the instance here
		if( sizeof($_SESSION['recently_viewed_products']) > $number ) {
			array_shift($_SESSION['recently_viewed_products']);
		}
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
		
		$show_variations = isset($instance['show_variations']) ? (bool)$instance['show_variations'] : false;
		
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
		
		// Show variations?
		echo '<p>';
		echo '<input type="checkbox" class="checkbox" id="' . $this->get_field_id('show_variations') . '" name="' . $this->get_field_name('show_variations') . '"' . checked( $show_variations ) . '/>';
		echo '<label for="' . $this->get_field_id('show_variations') . '"> ' . __( 'Show hidden product variations', 'jigoshop' ) . '</label>';
		echo '</p>';
	}
 }