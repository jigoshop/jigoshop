<?php
/**
 * Shopping Cart Widget
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
class Jigoshop_Widget_Cart extends WP_Widget {

    /**
     * Constructor
     * 
     * Setup the widget with the available options
     */
    public function __construct() {
    	
        $options = array(
        	'description' => __("Shopping Cart for the sidebar.", 'jigoshop')
        );
        
        // Create the widget
        parent::__construct('shopping_cart', __('Jigoshop: Shopping Cart', 'jigoshop'), $options);
    }

    /**
     * Widget
     * 
     * Display the widget in the sidebar
     *
     * @param	array	sidebar arguments
     * @param	array	instance
     */
    public function widget($args, $instance) {

		// Hide widget if page is the cart
        if (is_cart())
            return false;

        extract($args);
        
        // Set the widget title
        $title = ( ! empty($instance['title']) ) ? $instance['title'] : __('Cart', 'jigoshop');
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

		// Print the widget wrapper & title
        echo $before_widget;
        echo $before_title . $title . $after_title;
        
        // Get the contents of the cart
        $cart_contents = jigoshop_cart::$cart_contents;
        
        // If there are items in the cart print out a list of products
        if (sizeof($cart_contents) > 0) {
        	
        	echo '<ul class="cart_list">'; // Open the list
        
            foreach ($cart_contents as $key => $value) {
            	// Get product instance
                $_product = $value['data'];
                
                if ($_product->exists() AND $value['quantity'] > 0) {
                    echo '<li><a href="' . get_permalink($_product->id) . '">';
					
					// Print the product thumbnail image if exists else display placeholder
                    echo (has_post_thumbnail($_product->id))
                    		? get_the_post_thumbnail($_product->id, 'shop_tiny') 
                    		: jigoshop_get_image_placeholder( 'shop_tiny' );

					// Print the product title
                    echo '<span class="js_widget_product_title">' . apply_filters('jigoshop_cart_widget_product_title', $_product->get_title(), $_product) . '</span>';
                    echo '</a>';
                    
                    // Print the quantity & price per product
                    echo '<span class="js_widget_product_price">' . $value['quantity'].' &times; '.jigoshop_price($_product->get_price()) . '</span>';
                    echo '</li>';
                }
            }
            
            echo '</ul>'; // Close the list
            
            // Print the cart total
            echo '<p class="total"><strong>';
            	_e( ((get_option('jigoshop_prices_include_tax') == 'yes') ? 'Total' : 'Subtotal'), 'jigoshop');
            	echo ':</strong> ' . jigoshop_cart::get_cart_total();
            echo '</p>';

            do_action('jigoshop_widget_shopping_cart_before_buttons');

			// Print view cart & checkout buttons
            echo '<p class="buttons">'
					.'<a href="' . jigoshop_cart::get_cart_url() . '" class="button">' . __('View Cart &rarr;', 'jigoshop') . '</a>'
					.'<a href="' . jigoshop_cart::get_checkout_url() . '" class="button checkout">' . __('Checkout &rarr;', 'jigoshop') . '</a>';
            echo '</p>';
            
        } else {
        	echo '<span class="empty">' . __('No products in the cart.', 'jigoshop') . '</span>';
        }
		
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
    public function update($new_instance, $old_instance) {
    	$instance = $old_instance;
        $instance['title'] = strip_tags(stripslashes($new_instance['title']));
        return $instance;
    }

    /**
     * Form
     * 
     * Displays the form for the wordpress admin
     *
     * @param	array	instance
     */
    public function form($instance) {
    	// Get values from instance
    	$title = (isset($instance['title'])) ? esc_attr($instance['title']) : null;
    
    	// Widget title
    	echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">' . _e('Title:', 'jigoshop') . '</label>';
		echo '<input type="text" class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . $title . '" />';
       	echo '</p>';
    }

} // class Jigoshop_Widget_Cart