<?php
/**
 * Various hooks Jigoshop core uses
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Core
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

/**
 * Various hooks Jigoshop uses to do stuff. index:
 *
 *		- Add order item
 *		- When default permalinks are enabled, redirect shop page to post type archive url
 *		- Add to Cart
 *		- Clear cart
 *		- Restore an order via a link
 *		- Cancel a pending order
 *		- Download a file
 *		- Order Status completed - GIVE DOWNLOADABLE PRODUCT ACCESS TO CUSTOMER
 *
 **/

/**
 * Add order item
 *
 * Add order item via ajax
 *
 * @since 		1.0
 */
add_action('wp_ajax_jigoshop_add_order_item', 'jigoshop_add_order_item');

function jigoshop_add_order_item() {

	check_ajax_referer( 'add-order-item', 'security' );

	global $wpdb;

	$item_to_add = trim(stripslashes($_POST['item_to_add']));

	$post = '';

	// Find the item
	if (is_numeric($item_to_add)) :
		$post = get_post( $item_to_add );
	endif;

	if (!$post || ($post->post_type!=='product' && $post->post_type!=='product_variation')) :
		$post_id = $wpdb->get_var($wpdb->prepare("
			SELECT post_id
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
			WHERE $wpdb->postmeta.meta_key = 'SKU'
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_type = 'shop_product'
			AND $wpdb->postmeta.meta_value = '".$item_to_add."'
			LIMIT 1
		"));
		$post = get_post( $post_id );
	endif;

	if (!$post || ($post->post_type!=='product' && $post->post_type!=='product_variation')) :
		die();
	endif;

	if ($post->post_type=="product") :
		$_product = &new jigoshop_product( $post->ID );
	else :
		$_product = &new jigoshop_product_variation( $post->ID );
	endif;

	$loop = 0;
	?>
	<tr class="item">
		<td class="product-id">#<?php echo $_product->id; ?></td>
		<td class="variation-id"><?php if (isset($_product->variation_id)) echo $_product->variation_id; else echo '-'; ?></td>
		<td class="product-sku"><?php if ($_product->sku) echo $_product->sku; ?></td>
		<td class="name"><a href="<?php echo admin_url('post.php?post='. $_product->id .'&action=edit'); ?>"><?php echo $_product->get_title(); ?></a></td>
		<td class="variation"><?php
			if (isset($_product->variation_data)) :
				echo jigoshop_get_formatted_variation( $_product->variation_data, true );
			else :
				echo '-';
			endif;
		?></td>
		<td>
			<table class="meta" cellspacing="0">
				<tfoot>
					<tr>
						<td colspan="3"><button class="add_meta button"><?php _e('Add meta', 'jigoshop'); ?></button></td>
					</tr>
				</tfoot>
				<tbody></tbody>
			</table>
		</td>
		<?php do_action('jigoshop_admin_order_item_values', $_product); ?>
		<td class="quantity"><input type="text" name="item_quantity[]" placeholder="<?php _e('Quantity e.g. 2', 'jigoshop'); ?>" value="1" /></td>
		<td class="cost"><input type="text" name="item_cost[]" placeholder="<?php _e('Cost per unit ex. tax e.g. 2.99', 'jigoshop'); ?>" value="<?php echo $_product->get_price(); ?>" /></td>
		<td class="tax"><input type="text" name="item_tax_rate[]" placeholder="<?php _e('Tax Rate e.g. 20.0000', 'jigoshop'); ?>" value="<?php echo $_product->get_tax_base_rate(); ?>" /></td>
		<td class="center">
			<input type="hidden" name="item_id[]" value="<?php echo $_product->id; ?>" />
			<input type="hidden" name="item_name[]" value="<?php echo $_product->get_title(); ?>" />
            <input type="hidden" name="item_variation_id[]" value="<?php if ($_product->variation_id) echo $_product->variation_id; else echo ''; ?>" />
			<button type="button" class="remove_row button">&times;</button>
		</td>
	</tr>
	<?php

	// Quit out
	die();

}


/**
 * When default permalinks are enabled, redirect shop page to post type archive url
 **/
if (get_option( 'permalink_structure' )=="") add_action( 'init', 'jigoshop_shop_page_archive_redirect' );

function jigoshop_shop_page_archive_redirect() {

	if ( isset($_GET['page_id']) && $_GET['page_id'] == get_option('jigoshop_shop_page_id') ) :
		wp_safe_redirect( get_post_type_archive_link('product') );
		exit;
	endif;

}

/**
 * Remove from cart/update
 **/
add_action( 'init', 'jigoshop_update_cart_action' );

function jigoshop_update_cart_action() {

	// Remove from cart
	if ( isset($_GET['remove_item']) && is_numeric($_GET['remove_item'])  && jigoshop::verify_nonce('cart', '_GET')) :

		jigoshop_cart::set_quantity( $_GET['remove_item'], 0 );

		// Re-calc price
		//jigoshop_cart::calculate_totals();

		jigoshop::add_message( __('Cart updated.', 'jigoshop') );

		if ( isset($_SERVER['HTTP_REFERER'])) :
			wp_safe_redirect($_SERVER['HTTP_REFERER']);
			exit;
		endif;

	// Update Cart
	elseif (isset($_POST['update_cart']) && $_POST['update_cart']  && jigoshop::verify_nonce('cart')) :

		$cart_totals = $_POST['cart'];

		if (sizeof(jigoshop_cart::$cart_contents)>0) :
			foreach (jigoshop_cart::$cart_contents as $cart_item_key => $values) :

				if (isset($cart_totals[$cart_item_key]['qty'])) jigoshop_cart::set_quantity( $cart_item_key, $cart_totals[$cart_item_key]['qty'] );

			endforeach;
		endif;

		jigoshop::add_message( __('Cart updated.', 'jigoshop') );

	endif;

}

/**
 * Add to cart
 **/
add_action( 'init', 'jigoshop_add_to_cart_action' );

function jigoshop_add_to_cart_action($url = false)
{
    //if required param is not set or nonce is invalid then just ignore whole function
    if (empty($_GET['add-to-cart']) || !jigoshop::verify_nonce('add_to_cart', '_GET')) {
        return;
    }

    $product_added = false;
    
    //single product
    if (is_numeric($_GET['add-to-cart'])) {
        $product_id = (int) $_GET['add-to-cart'];
        $quantity = 1;
        if (isset($_POST['quantity'])) {
            $quantity = (int) $_POST['quantity'];
        }

        jigoshop_cart::add_to_cart($product_id, $quantity);
        
        $product_added = true;
    } else if ($_GET['add-to-cart'] == 'variation') { //variable product variation

        //variaton wasn't selected but user managed to submit a form
        if (empty($_POST['variation_id']) || !is_numeric($_POST['variation_id'])) {
            /* Link on product pages */
            jigoshop::add_error(__('Please choose product options&hellip;', 'jigoshop'));
            wp_redirect(get_permalink($_GET['product']));
            exit;
        } else {
            $product_id = (int) $_GET['product'];
            $variation_id = (int) $_POST['variation_id'];
            $quantity = 1;
            if (isset($_POST['quantity'])) {
                $quantity = (int) $_POST['quantity'];
            }

            $attributes = (array) maybe_unserialize(get_post_meta($product_id, 'product_attributes', true));
            $variations = array();
            $all_variations_set = true;

            foreach ($attributes as $attribute) {

                if ($attribute['variation'] !== 'yes') {
                    continue;
                }

                $attr_name = 'tax_' . sanitize_title($attribute['name']);
                if (!empty($_POST[$attr_name])) {
                    $variations[$attr_name] = $_POST[$attr_name];
                } else {
                    $all_variations_set = false;
                }
            }

            if ($all_variations_set && $variation_id > 0) { //all variation options are set
                jigoshop_cart::add_to_cart($product_id, $quantity, $variation_id, $variations);

                $product_added = true;
            } else {
                /* Link on product pages */
                jigoshop::add_error(__('Please choose product options&hellip;', 'jigoshop'));
                wp_redirect(get_permalink($_GET['product']));
                exit;
            }
        }
    } else if ($_GET['add-to-cart'] == 'group') { //grouped product
        // Group add to cart
        if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {

            $total_quantity = 0;

            foreach ($_POST['quantity'] as $item => $quantity) {
                $quantity = (int)$quantity;
                
                if ($quantity > 0) {
                    jigoshop_cart::add_to_cart($item, $quantity);

                    $total_quantity = $total_quantity + $quantity;
                }
            }

            if ($total_quantity == 0) {
                jigoshop::add_error(__('Please choose a quantity&hellip;', 'jigoshop'));
            } else {
                $product_added = true;
            }
        } else if ($_GET['product']) {
            /* Link on product pages */
            jigoshop::add_error(__('Please choose a product&hellip;', 'jigoshop'));
            wp_redirect(get_permalink($_GET['product']));
            exit;
        }
    }
    
    //if product was successfully added to the cart
    if ($product_added && get_option('jigoshop_directly_to_checkout', 'no') == 'no') {
		jigoshop::add_message(sprintf(__('<a href="%s" class="button">View Cart &rarr;</a> Product successfully added to your cart.', 'jigoshop'), jigoshop_cart::get_cart_url()));
    }

    $url = apply_filters('add_to_cart_redirect', $url);

    // If has custom URL redirect there
    if ($url) {
        wp_safe_redirect($url);
    }
    // Redirect directly to checkout if no error messages
    else if (get_option('jigoshop_directly_to_checkout', 'no') == 'yes' && jigoshop::error_count() == 0) {
        wp_safe_redirect(jigoshop_cart::get_checkout_url());
    }
    // Redirect directly to cart if no error messages
    else if (get_option('jigoshop_directly_to_checkout', 'cart') == 'cart'
             && jigoshop::error_count() == 0
    ) {
        wp_safe_redirect(jigoshop_cart::get_cart_url());
    }
    // Otherwise redirect to where they came
    else if (isset($_SERVER['HTTP_REFERER'])) {
        wp_safe_redirect($_SERVER['HTTP_REFERER']);
    }
    // If all else fails redirect to root
    else {
        wp_redirect(home_url());
    }

    exit;
}

/**
 * Clear cart
 **/
add_action( 'wp_header', 'jigoshop_clear_cart_on_return' );

function jigoshop_clear_cart_on_return() {

	if (is_page(get_option('jigoshop_thanks_page_id'))) :

		if (isset($_GET['order'])) $order_id = $_GET['order']; else $order_id = 0;
		if (isset($_GET['key'])) $order_key = $_GET['key']; else $order_key = '';
		if ($order_id > 0) :
			$order = &new jigoshop_order( $order_id );
			if ($order->order_key == $order_key) :
				jigoshop_cart::empty_cart();
			endif;
		endif;

	endif;

}

/**
 * Clear the cart after payment - order will be processing or complete
 **/
add_action( 'init', 'jigoshop_clear_cart_after_payment' );

function jigoshop_clear_cart_after_payment( $url = false ) {

	if (isset($_SESSION['order_awaiting_payment']) && $_SESSION['order_awaiting_payment'] > 0) :

		$order = &new jigoshop_order($_SESSION['order_awaiting_payment']);

		if ($order->id > 0 && ($order->status=='completed' || $order->status=='processing')) :

			jigoshop_cart::empty_cart();

			unset($_SESSION['order_awaiting_payment']);

		endif;

	endif;

}


/**
 * Process the login form
 **/
add_action('init', 'jigoshop_process_login');

function jigoshop_process_login() {

	if (isset($_POST['login']) && $_POST['login']) :

		jigoshop::verify_nonce('login');

		if ( !isset($_POST['username']) || empty($_POST['username']) ) jigoshop::add_error( __('Username is required.', 'jigoshop') );
		if ( !isset($_POST['password']) || empty($_POST['password']) ) jigoshop::add_error( __('Password is required.', 'jigoshop') );

		if (jigoshop::error_count()==0) :

			$creds = array();
			$creds['user_login'] = $_POST['username'];
			$creds['user_password'] = $_POST['password'];
			$creds['remember'] = true;
			$secure_cookie = is_ssl() ? true : false;
			$user = wp_signon( $creds, $secure_cookie );
			if ( is_wp_error($user) ) :
				jigoshop::add_error( $user->get_error_message() );
			else :
				if ( isset($_SERVER['HTTP_REFERER'])) {
					wp_safe_redirect($_SERVER['HTTP_REFERER']);
					exit;
				}
				wp_redirect(get_permalink(get_option('jigoshop_myaccount_page_id')));
				exit;
			endif;

		endif;

	endif;
}

/**
 * Process ajax checkout form
 */
add_action('wp_ajax_jigoshop-checkout', 'jigoshop_process_checkout');
add_action('wp_ajax_nopriv_jigoshop-checkout', 'jigoshop_process_checkout');

function jigoshop_process_checkout () {
	include_once jigoshop::plugin_path() . '/classes/jigoshop_checkout.class.php';

	jigoshop_checkout::instance()->process_checkout();

	die(0);
}


/**
 * Cancel a pending order - hook into init function
 **/
add_action('init', 'jigoshop_cancel_order');

function jigoshop_cancel_order() {

	if ( isset($_GET['cancel_order']) && isset($_GET['order']) && isset($_GET['order_id']) ) :

		$order_key = urldecode( $_GET['order'] );
		$order_id = (int) $_GET['order_id'];

		$order = &new jigoshop_order( $order_id );

		if ($order->id == $order_id && $order->order_key == $order_key && $order->status=='pending' && jigoshop::verify_nonce('cancel_order', '_GET')) :

			// Cancel the order + restore stock
			$order->cancel_order( __('Order cancelled by customer.', 'jigoshop') );

			// Message
			jigoshop::add_message( __('Your order was cancelled.', 'jigoshop') );

		elseif ($order->status!='pending') :

			jigoshop::add_error( __('Your order is no longer pending and could not be cancelled. Please contact us if you need assistance.', 'jigoshop') );

		else :

			jigoshop::add_error( __('Invalid order.', 'jigoshop') );

		endif;

		wp_safe_redirect(jigoshop_cart::get_cart_url());
		exit;

	endif;
}


/**
 * Download a file - hook into init function
 **/
add_action('init', 'jigoshop_download_product');

function jigoshop_download_product() {

	if ( isset($_GET['download_file']) && isset($_GET['order']) && isset($_GET['email']) ) :

		global $wpdb;

		$download_file = (int) urldecode($_GET['download_file']);
		$order = urldecode( $_GET['order'] );
		$email = urldecode( $_GET['email'] );

		if (!is_email($email)) wp_safe_redirect( home_url() );

		$downloads_remaining = $wpdb->get_var( $wpdb->prepare("
			SELECT downloads_remaining
			FROM ".$wpdb->prefix."jigoshop_downloadable_product_permissions
			WHERE user_email = '$email'
			AND order_key = '$order'
			AND product_id = '$download_file'
		;") );
		
		if ($downloads_remaining == '0') :
            wp_die( sprintf(__('Sorry, you have reached your download limit for this file. <a href="%s">Go to homepage &rarr;</a>', 'jigoshop'), home_url()) );
		else :
			if ($downloads_remaining>0) :
				$wpdb->update( $wpdb->prefix . "jigoshop_downloadable_product_permissions", array(
					'downloads_remaining' => $downloads_remaining - 1,
				), array(
					'user_email' => $email,
					'order_key' => $order,
					'product_id' => $download_file
				), array( '%d' ), array( '%s', '%s', '%d' ) );
			endif;

			// Download the file
			$file_path = ABSPATH . get_post_meta($download_file, 'file_path', true);

            $file_path = realpath($file_path);
            
            if (!file_exists($file_path) || is_dir($file_path) || !is_readable($file_path)) {
                wp_die( sprintf(__('File not found. <a href="%s">Go to homepage &rarr;</a>', 'jigoshop'), home_url()) );
            }

            $file_extension = strtolower(substr(strrchr($file_path,"."),1));

            switch ($file_extension) :
                case "pdf": $ctype="application/pdf"; break;
                case "exe": $ctype="application/octet-stream"; break;
                case "zip": $ctype="application/zip"; break;
                case "doc": $ctype="application/msword"; break;
                case "xls": $ctype="application/vnd.ms-excel"; break;
                case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
                case "gif": $ctype="image/gif"; break;
                case "png": $ctype="image/png"; break;
                case "jpe": case "jpeg": case "jpg": $ctype="image/jpg"; break;
                default: $ctype="application/force-download";
            endswitch;

			@ini_set('zlib.output_compression', 'Off');
			@set_time_limit(0);
			@session_start();
			@session_cache_limiter('none');
			@set_magic_quotes_runtime(0);
			@ob_end_clean();
			@session_write_close();

			header("Pragma: no-cache");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Robots: none");
			header("Content-Type: ".$ctype."");
			header("Content-Description: File Transfer");

          	if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
			    // workaround for IE filename bug with multiple periods / multiple dots in filename
			    $iefilename = preg_replace('/\./', '%2e', basename($file_path), substr_count(basename($file_path), '.') - 1);
			    header("Content-Disposition: attachment; filename=\"".$iefilename."\";");
			} else {
			    header("Content-Disposition: attachment; filename=\"".basename($file_path)."\";");
			}

			header("Content-Transfer-Encoding: binary");

            header("Content-Length: ".@filesize($file_path));
            @readfile("$file_path") or wp_die( sprintf(__('File not found. <a href="%s">Go to homepage &rarr;</a>', 'jigoshop'), home_url()) );
			exit;

		endif;

	endif;
}


/**
 * Order Status completed - GIVE DOWNLOADABLE PRODUCT ACCESS TO CUSTOMER
 **/
add_action('order_status_completed', 'jigoshop_downloadable_product_permissions');

function jigoshop_downloadable_product_permissions( $order_id ) {

	global $wpdb;

	$order = &new jigoshop_order( $order_id );

	if (sizeof($order->items)>0) foreach ($order->items as $item) :

		if ($item['id']>0) :
			$_product = &new jigoshop_product( $item['id'] );

			if ( $_product->exists && $_product->is_type('downloadable') ) :

				$user_email = $order->billing_email;

				if ($order->user_id>0) :
					$user_info = get_userdata($order->user_id);
					if ($user_info->user_email) :
						$user_email = $user_info->user_email;
					endif;
				else :
					$order->user_id = 0;
				endif;

				$limit = trim(get_post_meta($_product->id, 'download_limit', true));

				if (!empty($limit)) :
					$limit = (int) $limit;
				else :
					$limit = '';
				endif;

				// Downloadable product - give access to the customer
				$wpdb->insert( $wpdb->prefix . 'jigoshop_downloadable_product_permissions', array(
					'product_id' => $_product->id,
					'user_id' => $order->user_id,
					'user_email' => $user_email,
					'order_key' => $order->order_key,
					'downloads_remaining' => $limit
				), array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				) );

			endif;

		endif;

	endforeach;
}
