<?php
/*
Plugin Name: Jigoshop - WordPress eCommerce
Plugin URI: http://jigoshop.com
Description: An eCommerce plugin for wordpress.
Version: 0.9.7.5
Author: Jigowatt
Author URI: http://jigowatt.co.uk
Requires at least: 3.1
Tested up to: 3.1.3
Forked By: Robert Rhoades
*/

	@session_start();
	
	if (!defined("PHP_EOL")) define("PHP_EOL", "\r\n");

	load_plugin_textdomain('jigoshop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	
	register_activation_hook( __FILE__, 'install_jigoshop' );

/**
 * Include core files and classes
 **/
	
	include_once( 'classes/jigoshop.class.php' );
	include_once( 'jigoshop_taxonomy.php' );
	include_once( 'jigoshop_widgets.php' );
	include_once( 'jigoshop_shortcodes.php' );
	include_once( 'jigoshop_breadcrumbs.php' );
	include_once( 'jigoshop_templates.php' );
	include_once( 'jigoshop_emails.php' );
	include_once( 'jigoshop_query.php' );
	include_once( 'jigoshop_cron.php' );
	include_once( 'jigoshop_actions.php' );
	include_once( 'gateways/gateways.class.php' );
	include_once( 'gateways/gateway.class.php' );
	include_once( 'shipping/shipping.class.php' );
	include_once( 'shipping/shipping_method.class.php' );
	
	function jigoshop_load_core() {
		
		$include_files = array();
		
		// Classes
		$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/classes/*.php" ));
		
		// Shipping
		$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/shipping/*.php" ));
		
		// Payment Gateways
		$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/gateways/*.php" ));
		
		// Drop-ins (addons, premium features etc)
		$include_files = array_merge($include_files, (array) glob( dirname(__FILE__)."/drop-ins/*.php" ));

		if ($include_files) :
			foreach($include_files as $filename) :
				if (!empty($filename) && strstr($filename, 'php')) :
					include_once($filename);
				endif;
			endforeach;
		endif;
			
		$jigoshop 					= jigoshop::get();
		
		jigoshop_post_type();
		
		// Init class singletons
		$jigoshop_customer 			= jigoshop_customer::get();				// Customer class, sorts out session data such as location
		$jigoshop_cart 				= jigoshop_cart::get();					// Cart class, stores the cart contents
		$jigoshop_shipping 			= jigoshop_shipping::get();				// Shipping class. loads and stores shipping methods
		$jigoshop_payment_gateways 	= jigoshop_payment_gateways::get();		// Payment gateways class. loads and stores payment methods
		
		// Constants
		if (!defined('JIGOSHOP_USE_CSS')) define('JIGOSHOP_USE_CSS', true);
		
		// Init
		jigoshop_init();
	}
	
/**
 * Add Image sizes and post thumbnail support to wordpress
 **/

	add_theme_support( 'post-thumbnails' );
	add_image_size( 'shop_tiny', jigoshop::get_var('shop_tiny_w'), jigoshop::get_var('shop_tiny_h'), 'true' );
	add_image_size( 'shop_thumbnail', jigoshop::get_var('shop_thumbnail_w'), jigoshop::get_var('shop_thumbnail_h'), 'true' );
	add_image_size( 'shop_small', jigoshop::get_var('shop_small_w'), jigoshop::get_var('shop_small_h'), 'true' );
	add_image_size( 'shop_large', jigoshop::get_var('shop_large_w'), jigoshop::get_var('shop_large_h'), 'true' );
	
/**
 * Include admin area
 **/

	if (is_admin()) include_once( 'admin/jigoshop-admin.php' );
	
/**
 * Filters and hooks
 **/
	
	add_action( 'init', 'jigoshop_load_core', 0 );
	if (get_option('jigoshop_force_ssl_checkout')=='yes') add_action( 'wp_head', 'jigoshop_force_ssl');
	add_action( 'wp_footer', 'jigowatt_sharethis' );

/**
 * IIS compat fix/fallback
 **/
 
if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
	if (isset($_SERVER['QUERY_STRING'])) { $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; }
}


/**
 * Support for Import/Export
 * 
 * WordPress import should work - however, it fails to import custom product attribute taxonomies.
 * This code grabs the file before it is imported and ensures the taxonomies are created.
 **/

function jigoshop_import_start() {
	
	global $wpdb;
	
	$id = (int) $_POST['import_id'];
	$file = get_attached_file( $id );

	$parser = new WXR_Parser();
	$import_data = $parser->parse( $file );

	if (isset($import_data['posts'])) :
		$posts = $import_data['posts'];
		
		if ($posts && sizeof($posts)>0) foreach ($posts as $post) :
			
			if ($post['post_type']=='product') :
				
				if ($post['terms'] && sizeof($post['terms'])>0) :
					
					foreach ($post['terms'] as $term) :
						
						$domain = $term['domain'];
						
						if (strstr($domain, 'product_attribute_')) :
							
							// Make sure it exists!
							if (!taxonomy_exists( $domain )) :
								
								$nicename = ucfirst(str_replace('product_attribute_', '', $domain));
								
								// Create the taxonomy
								$wpdb->insert( $wpdb->prefix . "jigoshop_attribute_taxonomies", array( 'attribute_name' => $nicename, 'attribute_type' => 'text' ), array( '%s', '%s' ) );
								
								// Register the taxonomy now so that the import works!
								register_taxonomy( $domain,
							        array('product'),
							        array(
							            'hierarchical' => true,
							            'labels' => array(
							                    'name' => $nicename,
							                    'singular_name' => $nicename,
							                    'search_items' =>  __( 'Search ', 'jigoshop') . $nicename,
							                    'all_items' => __( 'All ', 'jigoshop') . $nicename,
							                    'parent_item' => __( 'Parent ', 'jigoshop') . $nicename,
							                    'parent_item_colon' => __( 'Parent ', 'jigoshop') . $nicename . ':',
							                    'edit_item' => __( 'Edit ', 'jigoshop') . $nicename,
							                    'update_item' => __( 'Update ', 'jigoshop') . $nicename,
							                    'add_new_item' => __( 'Add New ', 'jigoshop') . $nicename,
							                    'new_item_name' => __( 'New ', 'jigoshop') . $nicename
							            ),
							            'show_ui' => false,
							            'query_var' => true,
							            'rewrite' => array( 'slug' => strtolower(sanitize_title($nicename)), 'with_front' => false, 'hierarchical' => true ),
							        )
							    );
			
								update_option('jigowatt_update_rewrite_rules', '1');
								
							endif;
							
						endif;
						
					endforeach;
					
				endif;
				
			endif;
			
		endforeach;
		
	endif;

}

add_action('import_start', 'jigoshop_import_start');
 
 
### Functions #########################################################

function jigoshop_init() {
	
	@ob_start();
	
	add_role('customer', 'Customer', array(
	    'read' => true,
	    'edit_posts' => false,
	    'delete_posts' => false
	));
   
    if (JIGOSHOP_USE_CSS) wp_register_style('jigoshop_frontend_styles', jigoshop::plugin_url() . '/assets/css/frontend.css');
    
    wp_register_style('jigoshop_fancybox_styles', jigoshop::plugin_url() . '/assets/css/fancybox.css');
    wp_register_style('jqueryui_styles', jigoshop::plugin_url() . '/assets/css/ui.css');
    wp_register_script( 'fancybox', jigoshop::plugin_url() . '/assets/js/jquery.fancybox-1.3.4.pack.js', 'jquery', '1.0' );
    wp_register_script( 'blockui', jigoshop::plugin_url() . '/assets/js/blockui.js', 'jquery', '1.0' );
    wp_register_script( 'cookie', jigoshop::plugin_url() . '/assets/js/cookie.js', 'jquery', '1.0' );
    wp_register_script( 'scrollto', jigoshop::plugin_url() . '/assets/js/scrollto.js', 'jquery', 'jquery', '1.0' );
    wp_register_script( 'jigoshop_script', jigoshop::plugin_url() . '/assets/js/script.js.php', 'jquery', '1.0' );
    wp_register_script( 'jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js', 'jquery', '1.0' );
    wp_register_script( 'jquery.placeholder', jigoshop::plugin_url() . '/assets/js/jquery.placeholder.js', 'jquery', '1.0' );
    
    if (is_admin()) :
    
    	wp_register_style('jigoshop_admin_styles', jigoshop::plugin_url() . '/assets/css/admin.css');
   		wp_register_style('jigoshop_admin_datepicker_styles', jigoshop::plugin_url() . '/assets/css/datepicker.css');
    	wp_enqueue_style('jigoshop_admin_styles');
    	wp_enqueue_style('jigoshop_admin_datepicker_styles');
    	
    	wp_register_script( 'flot', jigoshop::plugin_url() . '/assets/js/jquery.flot.min.js' );
    	wp_enqueue_script('flot');
    	wp_enqueue_script('blockui');
    	wp_enqueue_script('cookie');
    	
    else :
    
    	wp_enqueue_style('jigoshop_frontend_styles');
    	wp_enqueue_style('jigoshop_fancybox_styles');
    	wp_enqueue_style('jqueryui_styles');
    	wp_enqueue_script('jquery');
    	wp_enqueue_script('jqueryui');
    	wp_enqueue_script('fancybox');
    	wp_enqueue_script('blockui');
    	wp_enqueue_script('cookie');
    	wp_enqueue_script('scrollto');
    	wp_enqueue_script('jigoshop_script');
    	wp_enqueue_script('jquery.placeholder');
    	
    endif;
}

add_filter('script_loader_src', 'jigoshop_script_query_string');

function jigoshop_script_query_string($src)
{
    if ( FALSE === strpos($src, 'script.js.php') ) return $src;

    $src = explode('?', $src);
    
    $load_scripts = array();
    
    if ( is_page(get_option('jigoshop_checkout_page_id')) || is_page(get_option('jigoshop_pay_page_id')) ) :
    	$load_scripts[] = 'checkout';
    endif;

    return $src[0] . '?load_scripts='.implode(',', $load_scripts);
}

/* 
	jigowatt_sharethis
		Adds social sharing code to footer
*/
function jigowatt_sharethis() {
	if (is_single() && get_option('jigoshop_sharethis')) :
		
		echo '<script type="text/javascript" src="https://w.sharethis.com/button/buttons.js"></script><script type="text/javascript">stLight.options({publisher:"'.get_option('jigoshop_sharethis').'", onhover: false});</script>';
		
	endif;
}
	
function is_cart() {
	if (is_page(get_option('jigoshop_cart_page_id'))) return true;
	return false;
}

function is_checkout() {
	if (
		is_page(get_option('jigoshop_checkout_page_id'))
	) return true;
	return false;
}

if (!function_exists('is_ajax')) {
	function is_ajax() {
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) return true;
		return false;
	}
}

function jigoshop_force_ssl() {
	if (is_checkout() && !is_ssl()) :
		wp_redirect( str_replace('http:', 'https:', get_permalink(get_option('jigoshop_checkout_page_id'))), 301 );
		exit;
	endif;
}

function jigoshop_force_ssl_images( $content ) {
	if (is_ssl()) :
		if (is_array($content)) :
			$content = array_map('jigoshop_force_ssl_images', $content);
		else :
			$content = str_replace('http:', 'https:', $content);
		endif;
	endif;
	return $content;
}
add_filter('post_thumbnail_html', 'jigoshop_force_ssl_images');
add_filter('widget_text', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_image_attributes', 'jigoshop_force_ssl_images');
add_filter('wp_get_attachment_url', 'jigoshop_force_ssl_images');

function get_jigoshop_currency_symbol() {
	$currency = get_option('jigoshop_currency');
	$currency_symbol = '';
	switch ($currency) :
		case 'AUD' :
		case 'BRL' :
		case 'CAD' :
		case 'MXN' :
		case 'NZD' :
		case 'HKD' :
		case 'SGD' :
		case 'USD' : $currency_symbol = '&#36;'; break;
		case 'EUR' : $currency_symbol = '&euro;'; break;
		case 'JPY' : $currency_symbol = '&yen;'; break;
		
		case 'CZK' :
		case 'DKK' :
		case 'HUF' :
		case 'ILS' :
		case 'MYR' :
		case 'NOK' :
		case 'PHP' :
		case 'PLN' :
		case 'SEK' :
		case 'CHF' :
		case 'TWD' :
		case 'THB' : $currency_symbol = $currency; break;
		
		case 'GBP' : 
		default    : $currency_symbol = '&pound;'; break;
	endswitch;
	return apply_filters('jigoshop_currency_symbol', $currency_symbol, $currency);
}

function jigoshop_price( $price ) {
	$currency_pos = get_option('jigoshop_currency_pos');
	$currency_symbol = get_jigoshop_currency_symbol();
	$price = (double) $price;
	
	switch ($currency_pos) :
		case 'left' :
			return $currency_symbol.number_format($price, 2);
		break;
		case 'right' :
			return number_format($price, 2).$currency_symbol;
		break;
		case 'left_space' :
			return $currency_symbol.' '.number_format($price, 2);
		break;
		case 'right_space' :
			return number_format($price, 2).' '.$currency_symbol;
		break;
	endswitch;
}

function jigoshop_let_to_num($v) {
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
    return $ret;
}

function jigowatt_clean( $var ) {
	return stripslashes(trim($var));
}

### Extra Review Field in comments #########################################################

function jigoshop_add_comment_rating($comment_id) {
	if ( isset($_POST['rating']) ) :
		if (!$_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0) $_POST['rating'] = 5; 
		add_comment_meta( $comment_id, 'rating', $_POST['rating'], true );
	endif;
}
add_action( 'comment_post', 'jigoshop_add_comment_rating', 1 );

function jigoshop_check_comment_rating($comment_data) {
	// If posting a comment (not trackback etc) and not logged in
	if ( isset($_POST['rating']) && empty($_POST['rating']) && $comment_data['comment_type']== '' ) {
		wp_die( __('Please rate the product.',"jigowatt") );
		exit;
	}
	return $comment_data;
}
add_filter('preprocess_comment', 'jigoshop_check_comment_rating', 0);	

### Comments #########################################################

function jigoshop_comments($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; global $post; ?>
	
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<div id="comment-<?php comment_ID(); ?>" class="comment_container">

  			<?php echo get_avatar( $comment, $size='60' ); ?>
			
			<div class="comment-text">
				<div class="star-rating" title="<?php echo get_comment_meta( $comment->comment_ID, 'rating', true ); ?>">
					<span style="width:<?php echo get_comment_meta( $comment->comment_ID, 'rating', true )*16; ?>px"><?php echo get_comment_meta( $comment->comment_ID, 'rating', true ); ?> <?php _e('out of 5', 'jigoshop'); ?></span>
				</div>
				<?php if ($comment->comment_approved == '0') : ?>
					<p class="meta"><em><?php _e('Your comment is awaiting approval','jigoshop'); ?></em></p>
				<?php else : ?>
					<p class="meta">
						<?php _e('Rating by','jigoshop'); ?> <strong class="reviewer vcard"><span class="fn"><?php comment_author(); ?></span></strong> <?php _e('on','jigoshop'); ?> <?php echo get_comment_date('M jS Y'); ?>:
					</p>
				<?php endif; ?>
  				<div class="description"><?php comment_text(); ?></div>
  				<div class="clear"></div>
  			</div>
			<div class="clear"></div>			
		</div>
	<?php
}
