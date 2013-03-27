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
 * @package             Jigoshop
 * @category            Widgets
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class Jigoshop_Widget_Cart extends WP_Widget {

    private $jigoshop_options;
    
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
		parent::__construct( 'jigoshop_cart', __( 'Jigoshop: Cart', 'jigoshop' ), $options );
        
        $this->jigoshop_options = Jigoshop_Base::get_options();
	}

	public function total_cart_items() {
		$total = 0;
		if ( ! empty( jigoshop_cart::$cart_contents )) foreach ( jigoshop_cart::$cart_contents as $cart_item_key => $values ) {
			$_product = $values['data'];
			$total += $_product->get_price() * $values['quantity'];
		}
		return $total;
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

		// Hide widget if page is the cart or checkout
		if ( is_cart() || is_checkout() )
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

					// Displays variations and cart item meta
					echo jigoshop_cart::get_item_data($value);
					
					// Print the quantity & price per product
					echo '<span class="js_widget_product_price">' . $value['quantity'].' &times; '. $_product->get_price_html() . '</span>';
				echo '</li>';
				}
			}

			echo '</ul>'; // Close the list

			// Print the cart total
			echo '<p class="total"><strong>';
			echo __( 'Subtotal', 'jigoshop' );
			echo ':</strong> ' . jigoshop_price( $this->total_cart_items() );
			echo '</p>';

			do_action( 'jigoshop_widget_cart_before_buttons' );

			// Print view cart & checkout buttons
			$view_cart_button_label	= isset($instance['view_cart_button'])	? $instance['view_cart_button']	: __( 'View Cart &rarr;', 'jigoshop' );
			$checkout_button_label	= isset($instance['checkout_button'])	? $instance['checkout_button']	: __( 'Checkout &rarr;', 'jigoshop' );
			
			echo '<p class="buttons">';
			echo '<a href="' . esc_attr( jigoshop_cart::get_cart_url() ) . '" class="button">' . __( $view_cart_button_label, 'jigoshop' ) . '</a>';
			echo '<a href="' . esc_attr( jigoshop_cart::get_checkout_url() ) . '" class="button checkout">' . __( $checkout_button_label, 'jigoshop' ) . '</a>';
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
		$instance['title']				= strip_tags( $new_instance['title'] );
		$instance['view_cart_button']	= strip_tags( $new_instance['view_cart_button'] );
		$instance['checkout_button']	= strip_tags( $new_instance['checkout_button'] );

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
		$title				= isset( $instance['title'] )				? esc_attr( $instance['title'] ) : null;
		$view_cart_button	= isset( $instance['view_cart_button'] )	? esc_attr( $instance['view_cart_button'] ) : 'View Cart &rarr;';
		$checkout_button	= isset( $instance['checkout_button'] )		? esc_attr( $instance['checkout_button'] ) : 'Checkout &rarr;';

		// Widget Title
		echo "
		<p>
			<label for='{$this->get_field_id( 'title' )}'>" . __( 'Title:', 'jigoshop' ) . "</label>
			<input class='widefat' id='{$this->get_field_id( 'title' )}' name='{$this->get_field_name( 'title' )}' type='text' value='{$title}' />
		</p>";
		
		// View cart button label
		echo "
		<p>
			<label for='{$this->get_field_id( 'view_cart_button' )}'>" . __( 'View cart button:', 'jigoshop' ) . "</label>
			<input class='widefat' id='{$this->get_field_id( 'view_cart_button' )}' name='{$this->get_field_name( 'view_cart_button' )}' type='text' value='{$view_cart_button}' />
		</p>";
		
		// Checkout button label
		echo "
		<p>
			<label for='{$this->get_field_id( 'checkout_button' )}'>" . __( 'Checkout button:', 'jigoshop' ) . "</label>
			<input class='widefat' id='{$this->get_field_id( 'checkout_button' )}' name='{$this->get_field_name( 'checkout_button' )}' type='text' value='{$checkout_button}' />
		</p>";
	}

} // class Jigoshop_Widget_Cart