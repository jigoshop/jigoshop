<?php
/**
 * Various functions Jigoshop gives to the plugin authors.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Checks if current Jigoshop version is at least a specified one.
 *
 * @param $version string Version string (i.e. 1.10.1)
 * @return bool
 */
function jigoshop_is_minumum_version($version)
{
	if(version_compare(JIGOSHOP_VERSION, $version, '<'))
	{
		return false;
	}

	return true;
}

/**
 * Adds notice for specified source (i.e. plugin name) that current Jigoshop version is not matched.
 *
 * Notice is added only if version is not at it's minimum.
 *
 * @param $source string Source name (used in message).
 * @param $version string Version string (i.e. 1.10.1).
 * @return bool Whether notice was added.
 */
function jigoshop_add_required_version_notice($source, $version)
{
	if(!jigoshop_is_minumum_version($version))
	{
		add_action('admin_notices', function() use ($source, $version) {
			$message = sprintf(__('<strong>%s</strong>: required Jigoshop version: %s. Current version: %s. Please upgrade.', 'jigoshop'), $source, $version, JIGOSHOP_VERSION);
			echo "<div class=\"error\"><p>${message}</p></div>";
		});

		return true;
	}

	return false;
}

/**
 * Format decimal numbers according to current settings.
 *
 * @param  float|string $number Expects either a float or a string with a decimal separator only (no thousands)
 * @param  mixed $dp number of decimal points to use, blank to use configured decimals or false to avoid all rounding.
 * @param  boolean $trim_zeros from end of string
 * @return string
 */
function jigoshop_format_decimal($number, $dp = false, $trim_zeros = false)
{
	$locale = localeconv();
	$options = Jigoshop_Base::get_options();
	$decimals = array(
		$options->get('jigoshop_price_decimal_sep'),
		$locale['decimal_point'],
		$locale['mon_decimal_point']
	);

	// Remove locale from string
	if (!is_float($number)) {
		$number = jigowatt_clean(str_replace($decimals, '.', $number));
	}

	if ($dp !== false) {
		$dp = intval($dp == "" ? $options->get('jigoshop_price_num_decimals') : $dp);
		$number = number_format(floatval($number), $dp, '.', '');

		// DP is false - don't use number format, just return a string in our format
	} elseif (is_float($number)) {
		$number = jigowatt_clean(str_replace($decimals, '.', strval($number)));
	}

	if ($trim_zeros && strstr($number, '.')) {
		$number = rtrim(rtrim($number, '0'), '.');
	}

	return $number;
}

function jigoshop_get_core_supported_themes() {
	return array( 'twentyfifteen', 'twentyfourteen', 'twentythirteen', 'twentyeleven', 'twentytwelve', 'twentyten' );
}

/**
 * Get total spent by customer
 *
 * @param  int $user_id
 * @return string
 */
function jigoshop_get_customer_total_spent($user_id)
{
	if (!$spent = get_user_meta($user_id, 'money_spent', true)) {
		/** @var $wpdb \wpdb */
		global $wpdb;

		$spent = $wpdb->get_results("SELECT meta2.meta_value
			FROM $wpdb->posts as posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
			LEFT JOIN {$wpdb->term_relationships} AS tr ON posts.ID = tr.object_id
			LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id

			WHERE   meta.meta_key       = 'customer_user'
			AND     meta.meta_value     = $user_id
			AND     posts.post_type     IN ('".implode("','", array('shop_order'))."')
			AND     posts.post_status   IN ( 'publish' )
			AND     meta2.meta_key      = 'order_data'
			AND     tt.taxonomy         = 'shop_order_status'
			AND     t.name              IN ( 'processing', 'completed' )
		");

		$spent = array_sum(array_map(function($order){
			$order = maybe_unserialize($order->meta_value);

			return $order['order_total'];
		}, $spent));

		update_user_meta($user_id, 'money_spent', $spent);
	}

	return $spent;
}

/**
 * Get total orders by customer
 *
 * @param  int $user_id
 * @return int
 */
function jigoshop_get_customer_order_count($user_id)
{
	if (!$count = get_user_meta($user_id, 'order_count', true)) {
		global $wpdb;

		$count = $wpdb->get_var("SELECT COUNT(*)
			FROM $wpdb->posts as posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS tr ON posts.ID = tr.object_id
			LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id

			WHERE   meta.meta_key       = 'customer_user'
			AND     posts.post_type     IN ('".implode("','", array('shop_order'))."')
			AND     posts.post_status   IN ('publish')
			AND     meta_value          = $user_id
			AND     tt.taxonomy         = 'shop_order_status'
			AND     t.name              IN ( 'processing', 'completed' )
		");

		update_user_meta($user_id, 'order_count', absint($count));
	}

	return absint($count);
}

if (!function_exists('jrto_enqueue_script')) {
	/**
	 * Enqueues script.
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * in_footer - is this script required to add to the footer?
	 *
	 * Proper locations: 'admin', 'frontend'
	 *
	 * @param string $location Location to load script on.
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the script.
	 * @param array $options List of options.
	 */
	function jrto_enqueue_script($location, $handle, $src = false, array $dependencies = array(), array $options = array())
	{
		if (class_exists('\Jigoshop\RoundTrip\Optimizer')) {
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			Jigoshop\RoundTrip\Optimizer\Helper\Script::enqueue($location, $handle, $src, $dependencies, $options);
		} else {
			$action = $location == 'admin' ? 'admin' : 'wp';
			add_action($action.'_enqueue_scripts', function() use ($handle, $src, $dependencies, $options){

				$page = isset($options['page']) ? (array)$options['page'] : array('all');
				if (is_jigoshop_page($page)) {
					$version = isset($options['version']) ? $options['version'] : false;
					$footer = isset($options['in_footer']) ? $options['in_footer'] : false;
					wp_enqueue_script($handle, $src, $dependencies, $version, $footer);
				}
			}, 10);
		}
	}
}

if (!function_exists('jrto_register_script')) {
	/**
	 * Registers script.
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * in_footer - is this script required to add to the footer?
	 *
	 * Proper locations: 'admin', 'frontend'
	 *
	 * @param string $location Location to load script on.
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the script.
	 * @param array $options List of options.
	 */
	function jrto_register_script($location, $handle, $src, array $dependencies = array(), array $options = array())
	{
		if (class_exists('Jigoshop\RoundTrip\Optimizer')) {
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			Jigoshop\RoundTrip\Optimizer\Helper\Script::register($location, $handle, $src, $dependencies, $options);
		} else {
			$action = $location == 'admin' ? 'admin' : 'wp';
			add_action($action.'_enqueue_scripts', function() use ($handle, $src, $dependencies, $options){

				$page = isset($options['page']) ? (array)$options['page'] : array('all');
				if (is_jigoshop_page($page)) {
					$version = isset($options['version']) ? $options['version'] : false;
					$footer = isset($options['in_footer']) ? $options['in_footer'] : false;
					wp_register_script($handle, $src, $dependencies, $version, $footer);
				}
			}, 10);
		}
	}
}

if (!function_exists('jrto_deregister_script')) {
	/**
	 * Removes registered script.
	 *
	 * Proper locations: 'admin', 'frontend'
	 *
	 * @param string $location Location to load script on.
	 * @param string $handle Handle name.
	 */
	function jrto_deregister_script($location, $handle)
	{
		if (class_exists('Jigoshop\RoundTrip\Optimizer')) {
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			Jigoshop\RoundTrip\Optimizer\Helper\Script::deregister($location, $handle);
		} else {
			wp_deregister_script($handle);
		}
	}
}

if (!function_exists('jrto_localize_script')) {
	/**
	 * Localizes script.
	 *
	 * @param string $handle Handle name.
	 * @param string $object Object name.
	 * @param array $value Value to localize.
	 */
	function jrto_localize_script($handle, $object, $value)
	{
		if (class_exists('Jigoshop\RoundTrip\Optimizer')) {
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			Jigoshop\RoundTrip\Optimizer\Helper\Script::localize($handle, $object, $value);
		} else {
			$action = is_admin() ? 'admin' : 'wp';
			add_action($action.'_enqueue_scripts', function() use ($handle, $object, $value){
				wp_localize_script($handle, $object, $value);
			}, 10);
		}
	}
}


if (!function_exists('jrto_enqueue_style')) {
	/**
	 * Enqueues style.
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * media - CSS media this style represents
	 *
	 * Proper locations: 'admin', 'frontend'
	 *
	 * @param string $location Location to load script on.
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the script.
	 * @param array $options List of options.
	 */
	function jrto_enqueue_style($location, $handle, $src = false, array $dependencies = array(), array $options = array())
	{
		if (class_exists('\Jigoshop\RoundTrip\Optimizer')) {
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			Jigoshop\RoundTrip\Optimizer\Helper\Style::enqueue($location, $handle, $src, $dependencies, $options);
		} else {
			$action = $location == 'admin' ? 'admin' : 'wp';
			add_action($action.'_enqueue_scripts', function() use ($handle, $src, $dependencies, $options){
				$version = isset($options['version']) ? $options['version'] : false;
				$media = isset($options['media']) ? $options['media'] : 'all';
				wp_enqueue_style($handle, $src, $dependencies, $version, $media);
			}, 10);
		}
	}
}

if (!function_exists('jrto_register_style')) {
	/**
	 * Registers style.
	 * Available options:
	 *   * version - Wordpress script version number
	 *   * media - CSS media this style represents
	 *
	 * Proper locations: 'admin', 'frontend'
	 *
	 * @param string $location Location to load script on.
	 * @param string $handle Handle name.
	 * @param bool $src Source file.
	 * @param array $dependencies List of dependencies to the script.
	 * @param array $options List of options.
	 */
	function jrto_register_style($location, $handle, $src, array $dependencies = array(), array $options = array())
	{
		if (class_exists('\Jigoshop\RoundTrip\Optimizer')) {
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			Jigoshop\RoundTrip\Optimizer\Helper\Style::register($handle, $src, $dependencies, $options);
		} else {
			$action = $location == 'admin' ? 'admin' : 'wp';
			add_action($action.'_enqueue_scripts', function() use ($handle, $src, $dependencies, $options){
				$version = isset($options['version']) ? $options['version'] : false;
				$media = isset($options['media']) ? $options['media'] : 'all';
				wp_register_style($handle, $src, $dependencies, $version, $media);
			}, 10);
		}
	}
}

if (!function_exists('jrto_deregister_style')) {
	/**
	 * Removes registered style.
	 *
	 * Proper locations: 'admin', 'frontend'
	 *
	 * @param string $location Location to load script on.
	 * @param string $handle Handle name.
	 */
	function jrto_deregister_style($location, $handle)
	{
		if (class_exists('Jigoshop\RoundTrip\Optimizer')) {
			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			Jigoshop\RoundTrip\Optimizer\Helper\Style::deregister($location, $handle);
		} else {
			wp_deregister_style($handle);
		}
	}
}
