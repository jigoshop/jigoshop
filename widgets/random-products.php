<?php
/**
 * Random Products Widget
 *
 * Displays a list of random products, inspired by WebFactory's Jigoshop Random Product Widget
 * http://codecanyon.net/item/jigoshop-random-product-widget/1371096?WT?ref=jigowatt
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
class Jigoshop_Widget_Random_Products extends WP_Widget {

	/**
	 * Constructor
	 *
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct() {
		$options = array(
			'classname'   => 'jigoshop_random_products',
			'description' => __( 'Lists a random selection of products on your site.', 'jigoshop' ),
		);

		// Create the widget
		parent::__construct( 'jigoshop_random_products', __('Jigoshop: Random Products', 'jigoshop' ), $options );
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

		// Start buffering
		ob_start();
		extract($args);

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			( $instance['title'] ) ? $instance['title'] : __( 'Random Products', 'jigoshop' ),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if ( ! $number = absint( $instance['number'] ) ) {
			$number = 5;
		}

		// Set up query
		$query_args = array(
			'posts_per_page' => $number,
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'orderby'        => 'rand',
			'meta_query'     => array(
				array(
					'key'       => 'visibility',
					'value'     => array('catalog', 'visible'),
					'compare'   => 'IN',
				),
			)
		);

		// Run the query
		$q = new WP_Query($query_args);

		// If there are products
		if ( $q->have_posts() ) {

			// Print the widget wrapper & title
			echo $before_widget;
			echo $before_title . $title . $after_title;

			// Open the list
			echo '<ul class="product_list_widget">';

			// Print out each product
			while($q->have_posts()) : $q->the_post();

				// Get new jigoshop_product instance
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
		ob_get_flush();
	}

	/**
	 * Update
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

		return $instance;
	}

	/**
	 * Form
	 *
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	function form( $instance ) {

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
			<input id='{$this->get_field_id( 'number' )}' name='{$this->get_field_name( 'number' )}' type='number' value='{$number}' size='3' />
		</p>";
	}

} // class Jigoshop_Widget_Random_Products