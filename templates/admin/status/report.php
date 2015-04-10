<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="updated jigoshop-message">
	<p><?php _e('Please copy and paste this information in your ticket when contacting support:', 'jigoshop'); ?> </p>
	<p class="submit"><a href="#" class="button-primary debug-report"><?php _e('Get System Report', 'jigoshop'); ?></a>
	<a class="skip button-primary" href="http://docs.woothemes.com/document/understanding-the-jigoshop-system-status-report/" target="_blank"><?php _e('Understanding the Status Report', 'jigoshop'); ?></a></p>
	<div id="debug-report">
		<textarea readonly="readonly"></textarea>
		<p class="submit"><button id="copy-for-support" class="button-primary" href="#" data-tip="<?php _e('Copied!', 'jigoshop'); ?>"><?php _e('Copy for Support', 'jigoshop'); ?></button></p>
	</div>
</div>
<br/>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WordPress Environment"><?php _e('WordPress Environment', 'jigoshop'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Home URL"><?php _e('Home URL', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The URL of your site\'s homepage.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo home_url(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Site URL"><?php _e('Site URL', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The root URL of your site.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo site_url(); ?></td>
		</tr>
		<tr>
			<td data-export-label="Jigoshop Version"><?php _e('Jigoshop Version', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The version of Jigoshop installed on your site.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo esc_html(JIGOSHOP_VERSION); ?></td>
		</tr>
		<tr>
			<td data-export-label="Jigoshop Database Version"><?php _e('Jigoshop Database Version', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The version of jigoshop that the database is formatted for. This should be the same as your jigoshop Version.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo esc_html(get_option('jigoshop_db_version')); ?></td>
		</tr>
		<tr>
			<td data-export-label="Log Directory Writable"><?php _e('Log Directory Writable', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Several Jigoshop extensions can write logs which makes debugging problems easier. The directory must be writable for this to happen.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (@fopen(JIGOSHOP_LOG_DIR.'test-log.log', 'a')) {
					echo '<mark class="yes">'.'&#10004; <code>'.JIGOSHOP_LOG_DIR.'</code></mark> ';
				} else {
					printf('<mark class="error">'.'&#10005; '.__('To allow logging, make <code>%s</code> writable or define a custom <code>JIGOSHOP_LOG_DIR</code>.', 'jigoshop').'</mark>', JIGOSHOP_LOG_DIR);
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="WP Version"><?php _e('WP Version', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The version of WordPress installed on your site.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php bloginfo('version'); ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Multisite"><?php _e('WP Multisite', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Whether or not you have WordPress Multisite enabled.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php if (is_multisite()) echo '&#10004;'; else echo '&ndash;'; ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Memory Limit"><?php _e('WP Memory Limit', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The maximum amount of memory (RAM) that your site can use at one time.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				preg_match('/^(\d+)(\w*)?$/', trim(WP_MEMORY_LIMIT), $memory);
				$memory_limit = $memory[1];
				if (isset($memory[2])) {
					switch ($memory[2]) {
						case 'M':
						case 'm':
							$memory_limit *= 1024;
						case 'K':
						case 'k':
							$memory_limit *= 1024;
					}
				}

				if ($memory_limit < JIGOSHOP_REQUIRED_WP_MEMORY*1024*1024) {
					echo '<mark class="error">'.sprintf(__('%s - We recommend setting memory to at least %dMB. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'jigoshop'), size_format($memory_limit), JIGOSHOP_REQUIRED_WP_MEMORY, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP').'</mark>';
				} else {
					echo '<mark class="yes">'.size_format($memory_limit).'</mark>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="WP Debug Mode"><?php _e('WP Debug Mode', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Displays whether or not WordPress is in Debug Mode.', 'jigoshop') . '">[?]</a>'; ?></td>
			<td><?php if (defined('WP_DEBUG') && WP_DEBUG) echo '<mark class="yes">'.'&#10004;'.'</mark>'; else echo '<mark class="no">'.'&ndash;'.'</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Language"><?php _e('Language', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The current language used by WordPress. Default = English', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo get_locale() ?></td>
		</tr>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Server Environment"><?php _e('Server Environment', 'jigoshop'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Server Info"><?php _e('Server Info', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Information about the web server that is currently hosting your site.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Version"><?php _e('PHP Version', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The version of PHP installed on your hosting server.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (version_compare(PHP_VERSION, JIGOSHOP_PHP_VERSION, '<')) {
					echo '<mark class="error">'.sprintf(__('%s - We recommend a minimum PHP version of %s. See: <a href="%s" target="_blank">How to update your PHP version</a>', 'jigoshop'), esc_html(PHP_VERSION), JIGOSHOP_PHP_VERSION, 'http://docs.woothemes.com/document/how-to-update-your-php-version/').'</mark>';
				} else {
					echo '<mark class="yes">'.esc_html(PHP_VERSION).'</mark>';
				}
				?></td>
		</tr>
		<?php if (function_exists('ini_get')) : ?>
			<tr>
				<td data-export-label="PHP Post Max Size"><?php _e('PHP Post Max Size', 'jigoshop'); ?>:</td>
				<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The largest filesize that can be contained in one post.', 'jigoshop') . '">[?]</a>'; ?></td>
				<td><?php echo size_format(jigoshop_let_to_num(ini_get('post_max_size'))); ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Time Limit"><?php _e('PHP Time Limit', 'jigoshop'); ?>:</td>
				<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'jigoshop').'">[?]</a>'; ?></td>
				<td><?php echo ini_get('max_execution_time'); ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Max Input Vars"><?php _e('PHP Max Input Vars', 'jigoshop'); ?>:</td>
				<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The maximum number of variables your server can use for a single function to avoid overloads.', 'jigoshop').'">[?]</a>'; ?></td>
				<td><?php echo ini_get('max_input_vars'); ?></td>
			</tr>
			<tr>
				<td data-export-label="SUHOSIN Installed"><?php _e('SUHOSIN Installed', 'jigoshop'); ?>:</td>
				<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'jigoshop').'">[?]</a>'; ?></td>
				<td><?php echo extension_loaded('suhosin') ? '&#10004;' : '&ndash;'; ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td data-export-label="MySQL Version"><?php _e('MySQL Version', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The version of MySQL installed on your hosting server.', 'jigoshop').'">[?]</a>'; ?></td>
			<td>
				<?php
				/** @global wpdb $wpdb */
				global $wpdb;
				echo $wpdb->db_version();
				?>
			</td>
		</tr>
		<tr>
			<td data-export-label="eAccelerator"><?php _e('eAccelerator', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('eAccelerator is deprecated and causes problems with Jigoshop.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (ini_get('eaccelerator.enable') == '1') {
					echo '<mark class="error">'.'&#10004; '.__('Enabled', 'jigoshop').'</mark>';
				} else {
					echo '&#10005;';
				}
				?></td>
		</tr>
		<tr>
			<td data-export-label="APC"><?php _e('APC', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('APC is deprecated and causes problems with Jigoshop.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (ini_get('apc.enable') == '1') {
					echo '<mark class="error">'.'&#10004; '.__('Enabled', 'jigoshop').'</mark>';
				} else {
					echo '&#10005;';
				}
				?></td>
		</tr>
		<tr>
			<td data-export-label="OpCache"><?php _e('OpCache', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('OpCache is new PHP optimizer and it is recommended to use with Jigoshop.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (ini_get('opcache.enable') == '1') {
					echo '<mark class="yes">'.'&#10004;'.'</mark>';
				} else {
					echo '&#10005;';
				}
				?></td>
		</tr>
		<tr>
			<td data-export-label="Short Open Tag"><?php _e('Short Open Tag', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Whether short tags are enabled, they are used by some older extensions.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo ini_get('short_open_tag') ? '&#10004;' : '&ndash;'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Allow URL fopen"><?php _e('Allow URL fopen', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Whether fetching remote files is allowed. This option is used by many Jigoshop extensions.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo ini_get('allow_url_fopen') ? '&#10004;' : '&ndash;'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Session"><?php _e('Session', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Whether PHP sessions are working properly.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo isset($_SESSION) ? '&#10004;' : '&ndash;'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Cookie Path"><?php _e('Cookie Path', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Path for which cookies are saved. This is important for sessions and session security.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo esc_html(ini_get('session.cookie_path')); ?></td>
		</tr>
		<tr>
			<td data-export-label="Save Path"><?php _e('Save Path', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Path where sessions are stored on the server. This is sometimes cause of login/logout problems.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo esc_html(ini_get('session.save_path')); ?></td>
		</tr>
		<tr>
			<td data-export-label="Use Cookies"><?php _e('Use Cookies', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Whether cookies are used to store PHP session on user\'s computer. Recommended.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (ini_get('session.use_cookies')) {
					echo '<mark class="yes">'.'&#10004;'.'</mark>';
				} else {
					echo '<mark class="error">'.'&#10005;'.'</mark>';
				}?></td>
		</tr>
		<tr>
			<td data-export-label="Use Only Cookies"><?php _e('Use Only Cookies', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Whether PHP uses only cookies to handle user sessions. This is important for security reasons.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (ini_get('session.use_only_cookies')) {
					echo '<mark class="yes">'.'&#10004;'.'</mark>';
				} else {
					echo '<mark class="error">'.'&#10005;'.'</mark>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Max Upload Size"><?php _e('Max Upload Size', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The largest filesize that can be uploaded to your WordPress installation.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo size_format(wp_max_upload_size()); ?></td>
		</tr>
		<tr>
			<td data-export-label="Default Timezone is UTC"><?php _e('Default Timezone is UTC', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The default timezone for your server.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				$default_timezone = date_default_timezone_get();
				if ('UTC' !== $default_timezone) {
					echo '<mark class="error">'.'&#10005; '.sprintf(__('Default timezone is %s - it should be UTC', 'jigoshop'), $default_timezone).'</mark>';
				} else {
					echo '<mark class="yes">'.'&#10004;'.'</mark>';
				} ?>
			</td>
		</tr>
		<?php
		$posting = array();

		// fsockopen/cURL
		$posting['fsockopen_curl']['name'] = 'fsockopen/cURL';
		$posting['fsockopen_curl']['help'] = '<a href="#" class="help_tip" data-tip="'.esc_attr__('Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'jigoshop').'">[?]</a>';

		if (function_exists('fsockopen') || function_exists('curl_init')) {
			$posting['fsockopen_curl']['success'] = true;
		} else {
			$posting['fsockopen_curl']['success'] = false;
			$posting['fsockopen_curl']['note'] = __('Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'jigoshop').'</mark>';
		}

		// SOAP
		$posting['soap_client']['name'] = 'SoapClient';
		$posting['soap_client']['help'] = '<a href="#" class="help_tip" data-tip="'.esc_attr__('Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'jigoshop').'">[?]</a>';

		if (class_exists('SoapClient')) {
			$posting['soap_client']['success'] = true;
		} else {
			$posting['soap_client']['success'] = false;
			$posting['soap_client']['note'] = sprintf(__('Your server does not have the <a href="%s">SOAP Client</a> class enabled - some gateway plugins which use SOAP may not work as expected.', 'jigoshop'), 'http://php.net/manual/en/class.soapclient.php').'</mark>';
		}

		// WP Remote Post Check
		$posting['wp_remote_post']['name'] = __('Remote Post', 'jigoshop');
		$posting['wp_remote_post']['help'] = '<a href="#" class="help_tip" data-tip="'.esc_attr__('PayPal uses this method of communicating when sending back transaction information.', 'jigoshop').'">[?]</a>';

		$response = wp_remote_post('https://www.paypal.com/cgi-bin/webscr', array(
			'sslverify' => false,
			'timeout' => 60,
			'user-agent' => 'Jigoshop/'.JIGOSHOP_VERSION,
			'body' => array(
				'cmd' => '_notify-validate'
			)
		));

		if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
			$posting['wp_remote_post']['success'] = true;
		} else {
			$posting['wp_remote_post']['note'] = __('wp_remote_post() failed. PayPal IPN won\'t work with your server. Contact your hosting provider.', 'jigoshop');
			if (is_wp_error($response)) {
				$posting['wp_remote_post']['note'] .= ' '.sprintf(__('Error: %s', 'jigoshop'), jigowatt_clean($response->get_error_message()));
			} else {
				$posting['wp_remote_post']['note'] .= ' '.sprintf(__('Status code: %s', 'jigoshop'), jigowatt_clean($response['response']['code']));
			}
			$posting['wp_remote_post']['success'] = false;
		}

		// WP Remote Get Check
		$posting['wp_remote_get']['name'] = __('Remote Get', 'jigoshop');
		$posting['wp_remote_get']['help'] = '<a href="#" class="help_tip" data-tip="'.esc_attr__('Jigoshop plugins may use this method of communication when checking for plugin updates.', 'jigoshop').'">[?]</a>';

		$response = wp_remote_get('http://www.woothemes.com/wc-api/product-key-api?request=ping&network='.(is_multisite() ? '1' : '0'));

		if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
			$posting['wp_remote_get']['success'] = true;
		} else {
			$posting['wp_remote_get']['note'] = __('wp_remote_get() failed. The jigoshop plugin updater won\'t work with your server. Contact your hosting provider.', 'jigoshop');
			if (is_wp_error($response)) {
				$posting['wp_remote_get']['note'] .= ' '.sprintf(__('Error: %s', 'jigoshop'), jigowatt_clean($response->get_error_message()));
			} else {
				$posting['wp_remote_get']['note'] .= ' '.sprintf(__('Status code: %s', 'jigoshop'), jigowatt_clean($response['response']['code']));
			}
			$posting['wp_remote_get']['success'] = false;
		}

		$posting = apply_filters('jigoshop_debug_posting', $posting);

		foreach ($posting as $post) {
			$mark = !empty($post['success']) ? 'yes' : 'error';
			?>
			<tr>
				<td data-export-label="<?php echo esc_html($post['name']); ?>"><?php echo esc_html($post['name']); ?>:</td>
				<td class="help"><?php echo isset($post['help']) ? $post['help'] : ''; ?></td>
				<td>
					<mark class="<?php echo $mark; ?>">
						<?php echo !empty($post['success']) ? '&#10004' : '&#10005'; ?>
						<?php echo !empty($post['note']) ? wp_kses_data($post['note']) : ''; ?>
					</mark>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Server Locale"><?php _e('Server Locale', 'jigoshop'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$locale = localeconv();
		$locale_help = array(
			'decimal_point' => __('The character used for decimal points.', 'jigoshop'),
			'thousands_sep' => __('The character used for a thousands separator.', 'jigoshop'),
			'mon_decimal_point' => __('The character used for decimal points in monetary values.', 'jigoshop'),
			'mon_thousands_sep' => __('The character used for a thousands separator in monetary values.', 'jigoshop'),
		);

		foreach ($locale as $key => $val) {
			if (in_array($key, array('decimal_point', 'mon_decimal_point', 'thousands_sep', 'mon_thousands_sep'))) {
				echo '<tr><td data-export-label="'.$key.'">'.$key.':</td><td class="help"><a href="#" class="help_tip" data-tip="'.esc_attr($locale_help[$key]).'">[?]</a></td><td>'.($val ? $val : __('N/A', 'jigoshop')).'</td></tr>';
			}
		}
		?>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Active Plugins (<?php echo count((array)get_option('active_plugins')); ?>)"><?php _e('Active Plugins', 'jigoshop'); ?> (<?php echo count((array)get_option('active_plugins')); ?>)</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$active_plugins = (array)get_option('active_plugins', array());

		if (is_multisite()) {
			$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
		}

		foreach ($active_plugins as $plugin) {
			$plugin_data = @get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
			$dirname = dirname($plugin);
			$version_string = '';
			$network_string = '';

			if (!empty($plugin_data['Name'])) {
				// link the plugin name to the plugin url if available
				$plugin_name = esc_html($plugin_data['Name']);

				if (!empty($plugin_data['PluginURI'])) {
					$plugin_name = '<a href="'.esc_url($plugin_data['PluginURI']).'" title="'.__('Visit plugin homepage', 'jigoshop').'" target="_blank">'.$plugin_name.'</a>';
				}
				?>
				<tr>
					<td><?php echo $plugin_name; ?></td>
					<td class="help">&nbsp;</td>
					<td><?php echo sprintf(_x('by %s', 'by author', 'jigoshop'), $plugin_data['Author']).' &ndash; '.esc_html($plugin_data['Version']).$version_string.$network_string; ?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<?php $options = Jigoshop_Base::get_options(); ?>
	<thead>
		<tr>
			<th colspan="3" data-export-label="Settings"><?php _e('Settings', 'jigoshop'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Taxes Enabled"><?php _e('Taxes Enabled', 'jigoshop') ?></td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Does your site have taxes enabled?', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo 'yes' === $options->get('jigoshop_calc_taxes') ? '<mark class="yes">'.'&#10004;'.'</mark>' : '<mark class="no">'.'&ndash;'.'</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Shipping Enabled"><?php _e('Shipping Enabled', 'jigoshop') ?></td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Does your site have shipping enabled?', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo 'yes' === $options->get('jigoshop_calc_shipping') ? '<mark class="yes">'.'&#10004;'.'</mark>' : '<mark class="no">'.'&ndash;'.'</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Force SSL"><?php _e('Force SSL', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Does your site force a SSL Certificate for transactions?', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo 'yes' === $options->get('jigoshop_force_ssl_checkout') ? '<mark class="yes">'.'&#10004;'.'</mark>' : '<mark class="no">'.'&ndash;'.'</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Currency"><?php _e('Currency', 'jigoshop') ?></td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('What currency prices are listed at in the catalog and which currency gateways will take payments in.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $options->get('jigoshop_currency'); ?> (<?php echo get_jigoshop_currency_symbol() ?>)</td>
		</tr>
		<tr>
			<td data-export-label="Currency Position"><?php _e('Currency Position', 'jigoshop') ?></td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The position of the currency symbol.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $options->get('jigoshop_currency_pos'); ?></td>
		</tr>
		<tr>
			<td data-export-label="Thousand Separator"><?php _e('Thousand Separator', 'jigoshop') ?></td>
			<td	class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The thousand separator of displayed prices.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $options->get('jigoshop_price_thousand_sep'); ?></td>
		</tr>
		<tr>
			<td data-export-label="Decimal Separator"><?php _e('Decimal Separator', 'jigoshop') ?></td>
			<td	class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The decimal separator of displayed prices.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $options->get('jigoshop_price_decimal_sep'); ?></td>
		</tr>
		<tr>
			<td data-export-label="Number of Decimals"><?php _e('Number of Decimals', 'jigoshop') ?></td>
			<td	class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The number of decimal points shown in displayed prices.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $options->get('jigoshop_price_num_decimals'); ?></td>
		</tr>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Jigoshop Pages"><?php _e('Jigoshop Pages', 'jigoshop'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$check_pages = array(
			__('Shop Base', 'jigoshop') => array(
				'option' => 'jigoshop_shop_page_id',
				'shortcode' => '',
				'help' => __('The URL of your Jigoshop shop\'s homepage (along with the Page ID).', 'jigoshop'),
			),
			__('Cart', 'jigoshop') => array(
				'option' => 'jigoshop_cart_page_id',
				'shortcode' => '['.apply_filters('jigoshop_cart_shortcode_tag', 'jigoshop_cart').']',
				'help' => __('The URL of your Jigoshop shop\'s cart (along with the page ID).', 'jigoshop'),
			),
			__('Checkout', 'jigoshop') => array(
				'option' => 'jigoshop_checkout_page_id',
				'shortcode' => '['.apply_filters('jigoshop_checkout_shortcode_tag', 'jigoshop_checkout').']',
				'help' => __('The URL of your Jigoshop shop\'s checkout (along with the page ID).', 'jigoshop'),
			),
			__('Pay', 'jigoshop') => array(
				'option' => 'jigoshop_pay_page_id',
				'shortcode' => '['.apply_filters('jigoshop_pay_shortcode_tag', 'jigoshop_pay').']',
				'help' => __('The URL of your Jigoshop shop\'s "Pay" page (along with the page ID).', 'jigoshop'),
			),
			__('Thanks', 'jigoshop') => array(
				'option' => 'jigoshop_thanks_page_id',
				'shortcode' => '['.apply_filters('jigoshop_thanks_shortcode_tag', 'jigoshop_thankyou').']',
				'help' => __('The URL of your Jigoshop shop\'s "Thank you" page (along with the page ID).', 'jigoshop'),
			),
			__('My Account', 'jigoshop') => array(
				'option' => 'jigoshop_myaccount_page_id',
				'shortcode' => '['.apply_filters('jigoshop_my_account_shortcode_tag', 'jigoshop_my_account').']',
				'help' => __('The URL of your Jigoshop shop\'s “My Account” Page (along with the page ID).', 'jigoshop'),
			),
			__('Edit Address', 'jigoshop') => array(
				'option' => 'jigoshop_edit_address_page_id',
				'shortcode' => '['.apply_filters('jigoshop_edit_address_shortcode_tag', 'jigoshop_edit_address').']',
				'help' => __('The URL of your Jigoshop shop\'s “Edit Address” Page (along with the page ID).', 'jigoshop'),
			),
			__('View Order', 'jigoshop') => array(
				'option' => 'jigoshop_view_order_page_id',
				'shortcode' => '['.apply_filters('jigoshop_view_order_shortcode_tag', 'jigoshop_view_order').']',
				'help' => __('The URL of your Jigoshop shop\'s “View Order” Page (along with the page ID).', 'jigoshop'),
			),
			__('Change Password', 'jigoshop') => array(
				'option' => 'jigoshop_change_password_page_id',
				'shortcode' => '['.apply_filters('jigoshop_change_password_shortcode_tag', 'jigoshop_change_password').']',
				'help' => __('The URL of your Jigoshop shop\'s “Change Password” Page (along with the page ID).', 'jigoshop'),
			),
			__('Track Order', 'jigoshop') => array(
				'option' => 'jigoshop_track_order_page_id',
				'shortcode' => '['.apply_filters('jigoshop_track_order_shortcode_tag', 'jigoshop_order_tracking').']',
				'help' => __('The URL of your Jigoshop shop\'s “Order Tracking” Page (along with the page ID).', 'jigoshop'),
			),
			__('Terms', 'jigoshop') => array(
				'option' => 'jigoshop_terms_page_id',
				'shortcode' => '',
				'help' => __('The URL of your Jigoshop shop\'s “Terms” Page (along with the page ID).', 'jigoshop'),
			),
		);

		$alt = 1;
		foreach ($check_pages as $page_name => $values) {
			$error = false;
			$page_id = $options->get($values['option']);

			if ($page_id) {
				$page_name = '<a href="'.get_edit_post_link($page_id).'" title="'.sprintf(__('Edit %s page', 'jigoshop'), esc_html($page_name)).'">'.esc_html($page_name).'</a>';
			} else {
				$page_name = esc_html($page_name);
			}

			echo '<tr><td data-export-label="'.esc_attr($page_name).'">'.$page_name.':</td>';
			echo '<td class="help"><a href="#" class="help_tip" data-tip="'.esc_attr($values['help']).'">[?]</a></td><td>';

			// Page ID check
			if (!$page_id) {
				echo '<mark class="error">'.__('Page not set', 'jigoshop').'</mark>';
				$error = true;
			} else {
				// Shortcode check
				if ($values['shortcode']) {
					$page = get_post($page_id);

					if (empty($page)) {
						echo '<mark class="error">'.sprintf(__('Page does not exist', 'jigoshop')).'</mark>';
						$error = true;
					} else if (!strstr($page->post_content, $values['shortcode'])) {
						echo '<mark class="error">'.sprintf(__('Page does not contain the shortcode: %s', 'jigoshop'), $values['shortcode']).'</mark>';
						$error = true;
					}
				}
			}

			if (!$error) {
				echo '<mark class="yes">#'.absint($page_id).' - '.str_replace(home_url(), '', get_permalink($page_id)).'</mark>';
			}

			echo '</td></tr>';
		}
		?>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Taxonomies"><?php _e('Taxonomies', 'jigoshop'); ?><?php echo ' <a href="#" class="help_tip" data-tip="'.esc_attr__('A list of taxonomy terms that can be used in regard to order/product statuses.', 'jigoshop').'">[?]</a>'; ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Product Types"><?php _e('Product Types', 'jigoshop'); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php
				$display_terms = array();
				$terms = get_terms('product_type', array('hide_empty' => 0));
				foreach ($terms as $term) {
					$display_terms[] = strtolower($term->name).' ('.$term->slug.')';
				}
				echo implode(', <br/>', array_map('esc_html', $display_terms));
			?></td>
		</tr>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Theme"><?php _e('Theme', 'jigoshop'); ?></th>
		</tr>
	</thead>
		<?php	$active_theme = wp_get_theme();	?>
	<tbody>
		<tr>
			<td data-export-label="Name"><?php _e('Name', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The name of the current active theme.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php /** @noinspection PhpUndefinedFieldInspection */ echo $active_theme->Name; ?></td>
		</tr>
		<tr>
			<td data-export-label="Version"><?php _e('Version', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The installed version of the current active theme.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				/** @noinspection PhpUndefinedFieldInspection */ echo $active_theme->Version;
			?></td>
		</tr>
		<tr>
			<td data-export-label="Author URL"><?php _e('Author URL', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The theme developers URL.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $active_theme->{'Author URI'}; ?></td>
		</tr>
		<tr>
			<td data-export-label="Child Theme"><?php _e('Child Theme', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Displays whether or not the current theme is a child theme.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				echo is_child_theme() ? '<mark class="yes">'.'&#10004;'.'</mark>' : '&#10005; &ndash; '.sprintf(__('If you\'re modifying Jigoshop or a parent theme you didn\'t build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'jigoshop'), 'http://codex.wordpress.org/Child_Themes');
			?></td>
		</tr>
		<?php
		if (is_child_theme()) :
			/** @noinspection PhpUndefinedFieldInspection */ $parent_theme = wp_get_theme($active_theme->Template);
			?>
		<tr>
			<td data-export-label="Parent Theme Name"><?php _e('Parent Theme Name', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The name of the parent theme.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $parent_theme->Name; ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Version"><?php _e('Parent Theme Version', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The installed version of the parent theme.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo  $parent_theme->Version; ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Author URL"><?php _e('Parent Theme Author URL', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('The parent theme developers URL.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php echo $parent_theme->{'Author URI'}; ?></td>
		</tr>
		<?php endif ?>
		<tr>
			<td data-export-label="Jigoshop Support"><?php _e('Jigoshop Support', 'jigoshop'); ?>:</td>
			<td class="help"><?php echo '<a href="#" class="help_tip" data-tip="'.esc_attr__('Displays whether or not the current active theme declares Jigoshop support.', 'jigoshop').'">[?]</a>'; ?></td>
			<td><?php
				if (!current_theme_supports('jigoshop') && !in_array($active_theme->template, jigoshop_get_core_supported_themes())) {
					echo '<mark class="error">'.__('Not Declared', 'jigoshop').'</mark>';
				} else {
					echo '<mark class="yes">'.'&#10004;'.'</mark>';
				}
			?></td>
		</tr>
	</tbody>
</table>
<table class="jigoshop_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Templates"><?php _e('Templates', 'jigoshop'); ?><?php echo ' <a href="#" class="help_tip" data-tip="'.esc_attr__('This section shows any files that are overriding the default jigoshop template pages.', 'jigoshop').'">[?]</a>'; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$template_paths = apply_filters('jigoshop_template_overrides_scan_paths', array('jigoshop' => JIGOSHOP_DIR.'/templates/'));
		$scanned_files = array();
		$found_files = array();

		foreach ($template_paths as $plugin_name => $template_path) {
			$scanned_files[$plugin_name] = Jigoshop_Admin_Status::scan_template_files($template_path);
		}

		foreach ($scanned_files as $plugin_name => $files) {
			foreach ($files as $file) {
				if (file_exists(get_stylesheet_directory().'/'.$file)) {
					$theme_file = get_stylesheet_directory().'/'.$file;
				} elseif (file_exists(get_stylesheet_directory().'/jigoshop/'.$file)) {
					$theme_file = get_stylesheet_directory().'/jigoshop/'.$file;
				} elseif (file_exists(get_template_directory().'/'.$file)) {
					$theme_file = get_template_directory().'/'.$file;
				} elseif (file_exists(get_template_directory().'/jigoshop/'.$file)) {
					$theme_file = get_template_directory().'/jigoshop/'.$file;
				} else {
					$theme_file = false;
				}

				if ($theme_file) {
					$core_version = Jigoshop_Admin_Status::get_file_version(JIGOSHOP_DIR.'/templates/'.$file);
					$theme_version = Jigoshop_Admin_Status::get_file_version($theme_file);

					if ($core_version && (empty($theme_version) || version_compare($theme_version, $core_version, '<'))) {
						$found_files[$plugin_name][] = sprintf(__('<code>%s</code> version <strong style="color:red">%s</strong> is out of date. The core version is %s', 'jigoshop'), str_replace(WP_CONTENT_DIR.'/themes/', '', $theme_file), $theme_version ? $theme_version : '-', $core_version);
					} else {
						$found_files[$plugin_name][] = sprintf('<code>%s</code>', str_replace(WP_CONTENT_DIR.'/themes/', '', $theme_file));
					}
				}
			}
		}

		if ($found_files) {
			foreach ($found_files as $plugin_name => $found_plugin_files) {
				?>
				<tr>
					<td data-export-label="Overrides"><?php _e('Overrides', 'jigoshop'); ?>
						(<?php echo $plugin_name; ?>):
					</td>
					<td class="help">&nbsp;</td>
					<td><?php echo implode(', <br/>', $found_plugin_files); ?></td>
				</tr>
			<?php
			}
		} else {
			?>
			<tr>
				<td data-export-label="Overrides"><?php _e('Overrides', 'jigoshop'); ?>:</td>
				<td class="help">&nbsp;</td>
				<td>&ndash;</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
<?php do_action('jigoshop_system_status_report'); ?>
<script type="text/javascript">
	jQuery(function($){
		$('a.help_tip').click(function(){
			return false;
		});
		$('a.debug-report').click(function(){
			var report = '';
			$('#status thead, #status tbody').each(function(){
				if($(this).is('thead')){
					var label = $(this).find('th:eq(0)').data('export-label') || $(this).text();
					report = report + "\n### " + $.trim(label) + " ###\n\n";
				} else {
					$('tr', $(this)).each(function(){
						var label = $(this).find('td:eq(0)').data('export-label') || $(this).find('td:eq(0)').text();
						var the_name = $.trim(label).replace(/(<([^>]+)>)/ig, ''); // Remove HTML
						var the_value = $.trim($(this).find('td:eq(2)').text());
						var value_array = the_value.split(', ');
						if(value_array.length > 1){
							// If value have a list of plugins ','
							// Split to add new line
							var temp_line = '';
							$.each(value_array, function(key, line){
								temp_line = temp_line + line + '\n';
							});
							the_value = temp_line;
						}
						report = report + '' + the_name + ': ' + the_value + "\n";
					});
				}
			});
			try {
				$("#debug-report").slideDown();
				$("#debug-report textarea").val(report).focus().select();
				$(this).fadeOut();
				return false;
			} catch(e) {
				console.log(e);
			}
			return false;
		});

		$('#copy-for-support').tipTip({
			'attribute': 'data-tip',
			'activation': 'click',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 0
		});
		$(document.body).on('copy', '#copy-for-support', function(e){
			e.clipboardData.clearData();
			e.clipboardData.setData('text/plain', $('#debug-report textarea').val());
			e.preventDefault();
		});
	});
</script>
