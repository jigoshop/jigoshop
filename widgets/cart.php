<?php
/**
 * Cart Widget
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Widgets
 * @author		Jigowatt
 * @since		1.0
 * @copyright	Copyright (c) 2011 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */
class Jigoshop_Widget_Cart extends WP_Widget {

	/**
	 * Constructor
	 * 
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct() {
		$options = array(
			'classname'		=> 'jigoshop_cart',
			'description'	=> __( 'Shopping Cart for the sidebar', 'jigoshop' )
		);

		// Create the widget
		parent::__construct( 'jigoshop-cart', __( 'Jigoshop: Cart', 'jigoshop' ), $options );
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

		// Hide widget if page is the cart
		if ( is_cart() )
			return false;

		extract( $args );

		// Set the widget title
		$title = apply_filters(
			'widget_title', 
			( $instance['title'] ) ? $instance['title'] : __( 'Cart', 'jigoshop' ), 
			$instance,
			$this->id_base
		);

		// Print the widget wrapper & title
		echo $before_widget;
		echo $before_title . $title . $after_title;

		// Get the contents of the cart
		$cart_contents = jigoshop_cart::$cart_contents;

		// If there are items in the cart print out a list of products
		if ( ! empty( $cart_contents ) ) {

			// Open the list
			echo '<ul class="cart_list">'; 
		
			foreach ( $cart_contents as $key => $value ) {

				// Get product instance
				$_product = $value['data'];

				if ( $_product->exists() && $value['quantity'] > 0 ) {
				echo '<li>';
					// Print the product image & title with a link to the permalink
					echo '<a href="' . esc_attr( get_permalink( $_product->id ) ) . '" title="' . esc_attr( $_product->get_title() ) . '">';
					
					// Print the product thumbnail image if exists else display placeholder
					echo (has_post_thumbnail( $_product->id ) )
							? get_the_post_thumbnail( $_product->id, 'shop_tiny' ) 
							: jigoshop_get_image_placeholder( 'shop_tiny' );

					// Print the product title
					echo '<span class="js_widget_product_title">' . $_product->get_title() . '</span>';
					echo '</a>';
					
					// Print the quantity & price per product
					echo '<span class="js_widget_product_price">' . $value['quantity'].' &times; '. $_product->get_price_html() . '</span>';
				echo '</li>';
				}
			}

			echo '</ul>'; // Close the list

			// Print the cart total
			echo '<p class="total"><strong>';
			echo __( ( ( get_option( 'jigoshop_prices_include_tax') == 'yes' ) ? 'Total' : 'Subtotal' ), 'jigoshop' );
			echo ':</strong> ' . jigoshop_cart::get_cart_total();
			echo '</p>';

			do_action( 'jigoshop_widget_cart_before_buttons' );

			// Print view cart & checkout buttons
			echo '<p class="buttons">';
			echo '<a href="' . esc_attr( jigoshop_cart::get_cart_url() ) . '" class="button">' . __( 'View Cart &rarr;', 'jigoshop' ) . '</a>';
			echo '<a href="' . esc_attr( jigoshop_cart::get_checkout_url() ) . '" class="button checkout">' . __( 'Checkout &rarr;', 'jigoshop' ) . '</a>';
			echo '</p>';

		} else {
			echo '<span class="empty">' . __( 'No products in the cart.', 'jigoshop' ) . '</span>';
		}

		// Print closing widget wrapper
		echo $after_widget;
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

		// Get instance data
		$title	= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : null;

		// Widget Title
		echo "
		<p>
			<label for='{$this->get_field_id( 'title' )}'>" . __( 'Title:', 'jigoshop' ) . "</label>
			<input class='widefat' id='{$this->get_field_id( 'title' )}' name='{$this->get_field_name( 'title' )}' type='text' value='{$title}' />
		</p>";
	}

} // class Jigoshop_Widget_Cart