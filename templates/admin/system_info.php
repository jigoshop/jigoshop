<?php
use Jigoshop\Helper\Formatter;

/**
 * @var $wpdb \WPDB Database.
 * @var $show_on_front string
 * @var $page_on_front string
 * @var $page_for_posts string
 */
?>
<div id="jigoshop-metaboxes-main" class="wrap jigoshop">
	<h2><?php _e('Jigoshop - system information','jigoshop') ?></h2>

	<p><?php echo sprintf(__('Use the information below when submitting technical support requests via <a href="%s" title="Jigoshop Support">Jigoshop Support</a>', 'jigoshop'), 'http://wordpress.org/support/plugin/jigoshop'); ?></p>

	<textarea readonly="readonly" id="system-info-textarea" rows="30" cols="100" title="<?php echo __('To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac)', 'jigoshop'); ?>">
### Begin System Info ###
Multi-site:               <?php echo (is_multisite() ? 'Yes' : 'No').PHP_EOL ?>

SITE_URL:                 <?php echo site_url().PHP_EOL; ?>
HOME_URL:                 <?php echo home_url().PHP_EOL; ?>

Jigoshop Version:         <?php echo \Jigoshop\Core::VERSION.PHP_EOL; ?>
WordPress Version:        <?php echo get_bloginfo('version').PHP_EOL; ?>

Browser:                  <?php echo $_SERVER['HTTP_USER_AGENT'].PHP_EOL; ?>

PHP Version:              <?php echo PHP_VERSION.PHP_EOL; ?>
MySQL Version:            <?php echo $wpdb->db_version().PHP_EOL; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'].PHP_EOL; ?>

PHP Memory Limit:         <?php echo ini_get('memory_limit').PHP_EOL; ?>
PHP Post Max Size:        <?php echo Formatter::letterToNumber(ini_get('post_max_size'))/(1024*1024).'MB'.PHP_EOL; ?>
PHP Upload Max File Size: <?php echo Formatter::letterToNumber(ini_get('upload_max_filesize'))/(1024*1024).'MB'.PHP_EOL; ?>

Show On Front:            <?php echo $show_on_front.PHP_EOL ?>
Page On Front:            <?php echo $page_on_front.PHP_EOL ?>
Page For Posts:           <?php echo $page_for_posts.PHP_EOL ?>

Session:                  <?php echo (isset($_SESSION) ? 'Enabled' : 'Disabled').PHP_EOL; ?>
Session Name:             <?php echo esc_html(ini_get('session.name')).PHP_EOL; ?>
Cookie Path:              <?php echo esc_html(ini_get('session.cookie_path')).PHP_EOL; ?>
Save Path:                <?php echo esc_html(ini_get('session.save_path')).PHP_EOL; ?>
Use Cookies:              <?php echo (ini_get('session.use_cookies') ? 'On' : 'Off').PHP_EOL; ?>
Use Only Cookies:         <?php echo (ini_get('session.use_only_cookies') ? 'On' : 'Off').PHP_EOL; ?>

WordPress Memory Limit:   <?php echo Formatter::letterToNumber(WP_MEMORY_LIMIT)/(1024*1024).'MB'.PHP_EOL; ?>
WP Table Prefix:          <?php echo 'Length: '.strlen($wpdb->prefix).' Status: '.(strlen($wpdb->prefix) > 16 ? 'ERROR: Too Long"' : 'Acceptable').PHP_EOL; ?>

WP_DEBUG:                 <?php echo (defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set').PHP_EOL ?>
DISPLAY ERRORS:           <?php echo (ini_get('display_errors') ? 'On (' . ini_get('display_errors') . ')' : 'N/A').PHP_EOL; ?>
ALLOW_URL_FOPEN:          <?php echo (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled').PHP_EOL; ?>
FSOCKOPEN:                <?php echo (function_exists('fsockopen') ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.').PHP_EOL; ?>

ACTIVE PLUGINS:
<?php
$active_plugins = get_option('active_plugins', array());
$plugins = get_plugins();

foreach ($plugins as $plugin_path => $plugin):
	if (!in_array($plugin_path, $active_plugins)){
		continue;
	}
?>
	<?php echo $plugin['Name']; ?>: <?php echo $plugin['Version'].PHP_EOL; ?>
<?php endforeach; ?>

CURRENT THEME:
<?php $theme_data = wp_get_theme(); ?>
	<?php echo $theme_data->Name . ': ' . $theme_data->Version.PHP_EOL; ?>

### End System Info ###
	</textarea>
</div>
