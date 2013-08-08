<?php
/**
 * Recent Reviews Widget
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

class Jigoshop_Widget_Recent_Reviews extends WP_Widget {

	/**
	 * Constructor
	 *
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct() {
		$options = array(
			'classname'	=> 'widget_recent_reviews',
			'description'	=> __( 'Display a list of your most recent product reviews', 'jigoshop' )
		);

		parent::__construct( 'recent-reviews', __( 'Jigoshop: Recent Reviews', 'jigoshop' ), $options );

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
		$cache = wp_cache_get( 'widget_recent_reviews', 'widget' );

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
		extract($args);

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __( 'Recent Reviews', 'jigoshop' ),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if ( ! $number = absint( $instance['number'] ) ) {
			$number = 5;
		}

		// Modify get_comments query to only include products which are visible
		add_filter( 'comments_clauses', array( &$this, 'where_product_is_visible' ) );

		// Get the latest reviews
		$comments = get_comments(array(
			'number'     => $number,
			'status'     => 'approve',
			'post_status'=> 'publish',
			'post_type'  => 'product',
		));

		// If there are products
		if( $comments ) {

			// Print the widget wrapper & title
			echo $before_widget;
			echo $before_title . $title . $after_title;

			// Open the list
			echo '<ul class="product_list_widget">';

			// Print out each product
			foreach( $comments as $comment ) {

				// Get new jigoshop_product instance
				$_product = new jigoshop_product( $comment->comment_post_ID );

				// Skip products that are invisible
				if( $_product->visibility == 'hidden' )
					continue;

				// TODO: Refactor this
				// Apply star size
				$star_size = apply_filters('jigoshop_star_rating_size_recent_reviews', 16);

				$rating = get_comment_meta( $comment->comment_ID, 'rating', true );

				echo '<li>';
					// Print the product image & title with a link to the permalink
					echo '<a href="'.esc_url( get_comment_link($comment->comment_ID) ).'">';

					// Print the product image
					echo ( has_post_thumbnail( $_product->id ) )
						? get_the_post_thumbnail( $_product->id,'shop_tiny' )
						: jigoshop_get_image_placeholder( 'shop_tiny' );

					echo '<span class="js_widget_product_title">' . $_product->get_title() . '</span>';
					echo '</a>';

					// Print the star rating
					echo "<div class='star-rating' title='{$rating}'>
						<span style='width:".($rating*$star_size)."px;'>{$rating} ".__( 'out of 5', 'jigoshop' )."</span>
					</div>";

					// Print the author
					printf( _x('by %1$s', 'author', 'jigoshop' ), get_comment_author($comment->comment_ID) );

				echo '</li>';
			}

			echo '</ul>'; // Close the list

			// Print closing widget wrapper
			echo $after_widget;

			// Remove the filter on comments to stop other queries from being manipulated
			remove_filter( 'comments_clauses', array(&$this, 'where_product_is_visible') );
		}

		// Flush output buffer and save to cache
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set( 'widget_recent_reviews', $cache, 'widget' );
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
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		// Flush the cache
		$this->flush_widget_cache();

		// Remove the cache entry from the options array
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_reviews'] ) ) {
			delete_option( 'widget_recent_reviews' );
		}

		return $instance;
	}

	/**
	 * Modifies get_comments query to only grab comments whose products are visible
	 *
	 * @param 	array 	Query Arguments
	 * @return 	array
	 */
	public function where_product_is_visible( $clauses ) {
		global $wpdb;

		// Only fetch comments whose products are visible
		$clauses['where']	.= " AND $wpdb->postmeta.meta_value = 'visible'";
		$clauses['join']	.= " LEFT JOIN $wpdb->postmeta ON($wpdb->comments.comment_post_ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = 'visibility')";;

		return $clauses;
	}

	/**
	 * Flush Widget Cache
	 *
	 * Flushes the cached output
	 */
	public function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_reviews', 'widget' );
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

} // class Jigoshop_Widget_Recent_Products
