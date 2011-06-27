<?php
/**
 * ACTIONS
 *
 * Various hooks Jigoshop uses to do stuff. index:
 *
 *		- Add to Cart
 *		- Restore an order via a link
 *		- Cancel a pending order
 *		- Download a file
 *		- Order Status completed - GIVE DOWNLOADABLE PRODUCT ACCESS TO CUSTOMER
 *
 **/

/**
 * Add to cart
 **/
add_action( 'init', 'jigoshop_add_to_cart' );

function jigoshop_add_to_cart( $url = false ) {
	
	if (isset($_GET['add-to-cart']) && $_GET['add-to-cart']) :
	
		if ( !jigoshop::verify_nonce('add_to_cart', '_GET') ) :

		elseif (is_numeric($_GET['add-to-cart'])) :
		
			$quantity = 1;
			if (isset($_POST['quantity'])) $quantity = $_POST['quantity'];
			jigoshop_cart::add_to_cart($_GET['add-to-cart'], $quantity);
			
			jigoshop::add_message( sprintf(__('<a href="%s" class="button">View Cart &rarr;</a> Product successfully added to your basket.', 'jigoshop'), jigoshop_cart::get_cart_url()) );
			
		elseif ($_GET['add-to-cart']=='group') :
			
			// Group add to cart
			if (isset($_POST['quantity']) && is_array($_POST['quantity'])) :
				
				foreach ($_POST['quantity'] as $item => $quantity) :
					if ($quantity>0) :
						jigoshop_cart::add_to_cart($item, $quantity);
						jigoshop::add_message( sprintf(__('<a href="%s" class="button">View Cart &rarr;</a> Product successfully added to your basket.', 'jigoshop'), jigoshop_cart::get_cart_url()) );
					endif;
				endforeach;
			
			elseif ($_GET['product']) :
				
				/* Link on product pages */
				jigoshop::add_error( __('Please choose a product&hellip;', 'jigoshop') );
				wp_redirect( get_permalink( $_GET['product'] ) );
				exit;
			
			endif; 
			
		endif;
		
		$url = apply_filters('add_to_cart_redirect', $url);
		
		// If has custom URL redirect there
		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
		
		// Otherwise redirect to where they came
		else if ( isset($_SERVER['HTTP_REFERER'])) {
			wp_safe_redirect($_SERVER['HTTP_REFERER']);
			exit;
		}
		
		// If all else fails redirect to root
		else {
			wp_safe_redirect('/');
			exit;
		}
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
		
		if ($downloads_remaining=='0') :
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

            if (!file_exists($file_path)) wp_die( sprintf(__('File not found. <a href="%s">Go to homepage &rarr;</a>', 'jigoshop'), home_url()) );
			
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
					'order_key' => $order->order_key.'3',
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