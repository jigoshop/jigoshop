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
 * @package             Jigoshop
 * @category            Widgets
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Widget_Top_Rated extends WP_Widget {

	/**
	 * Constructor
	 *
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct() {
		$options = array(
			'classname'	=> 'widget_top_rated',
			'description'	=> __( 'The best of the best on your site', 'jigoshop' )
		);

		parent::__construct( 'top-rated', __( 'Jigoshop: Top Rated Products', 'jigoshop' ), $options );

		// Flush cache after every save
		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
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

		// Get the most recent products from the cache
		$cache = wp_cache_get( 'widget_recent_products', 'widget' );

		// If no entry exists use array
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		// If cached get from the cache
		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return false;
		}

		// Start buffering
		ob_start();
		extract( $args );

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __( 'Top Rated Products', 'jigoshop' ),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if ( ! $number = absint( $instance['number'] ) ) {
			$number = 5;
		}

		// Set up query
		// Filter the $wpdb query
		add_filter( 'posts_clauses', array( &$this, 'order_by_rating' ) );

		// TODO: Only display products that are in stock
	    	$query_args = array(
	    		'posts_per_page' => $number,
	    		'post_type'      => 'product',
	    		'post_status'    => 'publish',
	    		'meta_query'     => array(
	    			array(
	    				'key'       => 'visibility',
	    				'value'     => array( 'catalog', 'visible' ),
	    				'compare'   => 'IN',
	    			),
    			)
	    	);

    		// Run the query
		$q = new WP_Query( $query_args );

		// If there are products
		if( $q->have_posts() ) {

			// Print the widget wrapper & title
			echo $before_widget;
			echo $before_title . $title . $after_title;

			// Open the list
			echo '<ul class="product_list_widget">';

			// Print out each product
			while( $q->have_posts() ) : $q->the_post(); $_product = new jigoshop_product( $q->post->ID );
			echo '<li>';
				// Print the title with a link to the permalink
				echo '<a href="'.esc_url( get_permalink() ).'" title="'.esc_attr( get_the_title() ).'">';

				// Print the product image
				echo ( has_post_thumbnail() )
					? the_post_thumbnail( 'shop_tiny' )
					: jigoshop_get_image_placeholder( 'shop_tiny' );

				echo '<span class="js_widget_product_title">' . get_the_title() . '</span>';
				echo '</a>';

				// Print the average rating with html wrappers
				echo $_product->get_rating_html( 'sidebar' );

				// Print the price with html wrappers
				echo '<span class="js_widget_product_price">' . $_product->get_price_html() . '</span>';
			echo '</li>';
			endwhile;

			echo '</ul>'; // Close the list

			// Print closing widget wrapper
			echo $after_widget;

			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();
			remove_filter( 'posts_clauses', array( &$this, 'order_by_rating' ) );
		}

		// Flush output buffer and save to cache
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set( 'widget_recent_products', $cache, 'widget' );
	}

	// Shouldn't we be left joining products onto comments rather than comments onto products?
	// Reason: Not all products have comments & its a waste of query time
	public function order_by_rating( $clauses ) {
		global $wpdb;

		$clauses['where'] 	.= " AND $wpdb->commentmeta.meta_key = 'rating' ";

		$clauses['join']		.= "
			LEFT JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
			LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
		";

		$clauses['orderby']	= "$wpdb->commentmeta.meta_value DESC";

		$clauses['groupby']	= "$wpdb->posts.ID";

		return $clauses;
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
		$instance['title']	= strip_tags( $new_instance['title'] );
		$instance['number']	= absint( $new_instance['number'] );

		// Flush the cache
		$this->flush_widget_cache();

		// Remove the cache entry from the options array
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_top_rated'] ) ) {
			delete_option( 'widget_top_rated' );
		}

		return $instance;
	}

	/**
	 * Flush Widget Cache
	 *
	 * Used to flush the cached output
	 */
	public function flush_widget_cache() {
		wp_cache_delete( 'widget_top_rated', 'widget' );
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
		$title 		= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : null;
		$number 	= isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;

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