<?php
/**
 * Recently Viewed Products Widget
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

 class Jigoshop_Widget_Recently_Viewed_Products extends WP_Widget {

 	/**
	 * Constructor
	 *
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct() {
		$options = array(
			'classname'	=> 'widget_recently_viewed_products',
			'description'	=> __( 'A list of your customers most recently viewed products', 'jigoshop' )
		);

		// Create the widget
		parent::__construct( 'recently_viewed_products', __( 'Jigoshop: Recently Viewed', 'jigoshop' ), $options );

		// Flush cache after every save
		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );

		// Attach the tracker to the product view action
		add_action( 'jigoshop_before_single_product', array( &$this, 'jigoshop_product_view_tracker' ), 10, 2 );
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
	public function widget( $args, $instance ) {

		// Get the most recently viewed products from the cache
		$cache = wp_cache_get( 'widget_recently_viewed_products', 'widget' );

		// If no entry exists use array
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		// If cached get from the cache
		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return false;
		}

		// Check if session contains recently viewed products
		if ( empty( jigoshop_session::instance()->recently_viewed_products ) )
			return false;

		// Start buffering the output
		ob_start();
		extract($args);

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Recently Viewed Products', 'jigoshop'),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if( ! $number = absint( $instance['number'] ) ) {
			$number = 5;
		}

		// Set up query
		$query_args = array(
			'posts_per_page' => $number,
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'nopaging'       => true,
			'post__in'       => jigoshop_session::instance()->recently_viewed_products,
			'orderby'        => 'date', // TODO: Not ideal as it doesn't order latest first
			'meta_query'     => array(
				array(
					'key'       => 'visibility',
					'value'     => array('catalog', 'visible'),
					'compare'   => 'IN',
				),
			)
		);

		// Run the query
		$q = new WP_Query( $query_args );

		if( $q->have_posts() ) {

			// Print the widget wrapper & title
			echo $before_widget;
			echo $before_title . $title . $after_title;

			// Open the list
			echo '<ul class="product_list_widget recently_viewed_products">';

			// Print out each produt
			while( $q->have_posts() ) : $q->the_post();

				// Get new jigoshop_product instance
				$_product = new jigoshop_product(get_the_ID());

			 	echo '<li>';

			 		//print the product title with a permalink
			 		echo '<a href="'.get_permalink().'" title="'.esc_attr( get_the_title() ).'">';

			 		// Print the product image
					echo (has_post_thumbnail())
						? the_post_thumbnail('shop_tiny')
						: jigoshop_get_image_placeholder('shop_tiny');

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
		wp_cache_set( 'widget_recent_products', $cache, 'widget' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		// Flush the cache
		$this->flush_widget_cache();

		// Unset the session array
		unset( jigoshop_session::instance()->recently_viewed_products );

		// Remove the cache entry from the options array
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recently_viewed_products'] ) ) {
			delete_option( 'widget_recently_viewed_products' );
		}

		return $instance;
	}

	/**
	 * Flush Widget Cache
	 *
	 * Flushes the cached output
	 */
	public function flush_widget_cache() {
		wp_cache_delete( 'widget_recently_viewed_products', 'widget' );
	}

	/**
	 * Logs viewed products into the session
	 *
	 * @return void
	 **/
	public function jigoshop_product_view_tracker( $post, $_product ) {

		$instance = get_option('widget_recently_viewed_products');
		$number = 0;
		if ( ! empty( $instance )) foreach ( $instance as $index => $entry ) {
			if ( is_array( $entry )) foreach ( $entry as $key => $value ) {
				if ( $key == 'number' ) {
					$number = $value;
					break;
				}
			}
		}
 		if ( ! $number ) return false; // stop the show!

		// Check if we already have some data
		if ( ! is_array( jigoshop_session::instance()->recently_viewed_products ) ) {
			$viewed = array();
			jigoshop_session::instance()->recently_viewed_products = $viewed;
		}

		// If the product isn't in the list, add it
		if ( ! in_array( $post->ID, jigoshop_session::instance()->recently_viewed_products) ) {
			$viewed = jigoshop_session::instance()->recently_viewed_products;
			$viewed[] = $post->ID;
			jigoshop_session::instance()->recently_viewed_products = $viewed;
		}

		if ( sizeof( jigoshop_session::instance()->recently_viewed_products) > $number ) {
			$viewed = jigoshop_session::instance()->recently_viewed_products;
			array_shift( $viewed );
			jigoshop_session::instance()->recently_viewed_products = $viewed;
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
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : null;
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;

		// Widget Title
		echo "
		<p>
			<label for='{$this->get_field_id( 'title' )}'>" . __( 'Title:', 'jigoshop' ) . "</label>
			<input class='widefat' id='{$this->get_field_id( 'title' )}' name='{$this->get_field_name( 'title' )}' type='text' value='{$title}' />
		</p>";

		// Number of posts to fetch
		echo "
		<p>
			<label for='{$this->get_field_id( 'number' )}'>" . __( 'Number of products to show:', 'jigoshop' ) . "</label>
			<input id='{$this->get_field_id( 'number' )}' name='{$this->get_field_name( 'number' )}' type='number' value='{$number}' />
		</p>";
	}
 }