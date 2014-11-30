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

	<p><?= sprintf(__('Use the information below when submitting technical support requests via <a href="%s" title="Jigoshop Support">Jigoshop Support</a>', 'jigoshop'), 'http://wordpress.org/support/plugin/jigoshop'); ?></p>

	<textarea readonly="readonly" id="system-info-textarea" rows="30" cols="100" title="<?= __('To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac)', 'jigoshop'); ?>">
### Begin System Info ###
Multi-site:               <?= (is_multisite() ? 'Yes' : 'No').PHP_EOL ?>

SITE_URL:                 <?= site_url().PHP_EOL; ?>
HOME_URL:                 <?= home_url().PHP_EOL; ?>

Jigoshop Version:         <?= \Jigoshop\Core::VERSION.PHP_EOL; ?>
WordPress Version:        <?= get_bloginfo('version').PHP_EOL; ?>

Browser:                  <?= $_SERVER['HTTP_USER_AGENT'].PHP_EOL; ?>

PHP Version:              <?= PHP_VERSION.PHP_EOL; ?>
MySQL Version:            <?= $wpdb->db_version().PHP_EOL; ?>
Web Server Info:          <?= $_SERVER['SERVER_SOFTWARE'].PHP_EOL; ?>

PHP Memory Limit:         <?= ini_get('memory_limit').PHP_EOL; ?>
PHP Post Max Size:        <?= Formatter::letterToNumber(ini_get('post_max_size'))/(1024*1024).'MB'.PHP_EOL; ?>
PHP Upload Max File Size: <?= Formatter::letterToNumber(ini_get('upload_max_filesize'))/(1024*1024).'MB'.PHP_EOL; ?>

Show On Front:            <?= $show_on_front.PHP_EOL ?>
Page On Front:            <?= $page_on_front.PHP_EOL ?>
Page For Posts:           <?= $page_for_posts.PHP_EOL ?>

Session:                  <?= (isset($_SESSION) ? 'Enabled' : 'Disabled').PHP_EOL; ?>
Session Name:             <?= esc_html(ini_get('session.name')).PHP_EOL; ?>
Cookie Path:              <?= esc_html(ini_get('session.cookie_path')).PHP_EOL; ?>
Save Path:                <?= esc_html(ini_get('session.save_path')).PHP_EOL; ?>
Use Cookies:              <?= (ini_get('session.use_cookies') ? 'On' : 'Off').PHP_EOL; ?>
Use Only Cookies:         <?= (ini_get('session.use_only_cookies') ? 'On' : 'Off').PHP_EOL; ?>

WordPress Memory Limit:   <?= Formatter::letterToNumber(WP_MEMORY_LIMIT)/(1024*1024).'MB'.PHP_EOL; ?>
WP Table Prefix:          <?= 'Length: '.strlen($wpdb->prefix).' Status: '.(strlen($wpdb->prefix) > 16 ? 'ERROR: Too Long"' : 'Acceptable').PHP_EOL; ?>

WP_DEBUG:                 <?= (defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set').PHP_EOL ?>
DISPLAY ERRORS:           <?= (ini_get('display_errors') ? 'On (' . ini_get('display_errors') . ')' : 'N/A').PHP_EOL; ?>
ALLOW_URL_FOPEN:          <?= (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled').PHP_EOL; ?>
FSOCKOPEN:                <?= (function_exists('fsockopen') ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.').PHP_EOL; ?>

ACTIVE PLUGINS:
<?php
$active_plugins = get_option('active_plugins', array());
$plugins = get_plugins();

foreach ($plugins as $plugin_path => $plugin):
	if (!in_array($plugin_path, $active_plugins)){
		continue;
	}
?>
	<?= $plugin['Name']; ?>: <?= $plugin['Version'].PHP_EOL; ?>
<?php endforeach; ?>

CURRENT THEME:
<?php $theme_data = wp_get_theme(); ?>
	<?= $theme_data->Name . ': ' . $theme_data->Version.PHP_EOL; ?>

### End System Info ###
	</textarea>
</div>
