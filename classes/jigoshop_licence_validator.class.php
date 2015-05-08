<?php

/**
 * Jigoshop Licence Validation Class used by downloadable digital products
 * Used for validation actions of product licencing from a selling shop
 * Used for WordPress auto update notices of updates available from a selling shop
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Extensions
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 * @version 1.3 - 2014-06-22
 */
class jigoshop_licence_validator
{
	private static $instance = false; /* Product ID from the selling shop */
	private static $plugins = array(); /* full server path to this plugin folder */
	private static $checked = array(); /* full server path to this plugin folder */
	private $identifier; /* full server path to this plugin main file */
	private $path; /* this plugin slug (plugin_directory/plugin_file.php) */
	private $file; /* this plugin file slug (plugin_filename without .php) */
	private $plugin_slug; /* actual name of this plugin */
	private $file_slug; /* currently installed version */
	private $title; /* the selling shop URL of this product */
	private $version;
	private $home_shop_url;
	private $validator_token = 'jigoshop-licence-validator';
	private $validator_prefix = 'jigoshop_licence_validator_';
	private $override_home_url_= false;
	private $custom_home_url = '';

	/**
	 * Constructor for Licence Validator in each plugin
	 *
	 * @param string $file - full server path to the main plugin file
	 * @param string $identifier - selling Shop Product ID
	 * @param string $home_shop_url - selling Shop URL of this plugin (product)
	 */
	public function __construct($file, $identifier, $home_shop_url)
	{

		$info = get_file_data($file, array('Title' => 'Plugin Name', 'Version' => 'Version', 'Url' => 'Plugin URI'), 'plugin');

		$this->identifier = $identifier;
		$this->file = $file;
		$this->path = plugin_dir_path($this->file);
		$this->plugin_slug = plugin_basename($this->file);
		list ($a, $b) = explode('/', $this->plugin_slug);
		$this->file_slug = str_replace('.php', '', $b);
		$this->title = $info['Title'];
		$this->version = $info['Version'];
		$this->plugin_url = $info['Url'];
		$this->home_shop_url = $home_shop_url;
		$this->override_home_url = get_option('jigoshop_license_override_home_url', false);
		$this->custom_home_url = get_option('jigoshop_license_custom_home_url', '');

		if($this->override_home_url){
			$this->home_shop_url = $this->custom_home_url;
		}

		self::$plugins[$this->identifier] = array(
			'version' => $this->version,
			'plugin_slug' => $this->plugin_slug,
			'file_slug' => $this->file_slug,
			'path' => $this->path,
			'title' => $this->title,
		);

		if (!self::$instance) {
			self::$instance = true;

			add_action('admin_menu', array($this, 'register_nav_menu_link'));
			// Define the alternative response for information checking
			add_filter('plugins_api', array($this, 'get_update_info'), 20, 3);
		}

		// define the alternative API for updating checking
		add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
		add_action('in_plugin_update_message-'.$this->plugin_slug, array($this, 'in_plugin_update_message'), 10, 2);
	}

	/**
	 * Is Licence Active for this plugin
	 * All plugins should call this early in their existance to check if their licence is valid
	 * Allow plugin to function normally if it is and limit or disable functionality otherwise
	 *
	 * @return boolean
	 */
	public function is_licence_active()
	{
		$active = $this->is_active();

		if (!$active) {
			add_action('admin_notices', array($this, 'display_inactive_plugin_warning'));
		}

		return $active;
	}

	private function is_active()
	{
		$keys = $this->get_keys();
		return (isset($keys[$this->identifier]['status']) && $keys[$this->identifier]['status']);
	}

	/**
	 * Returns a set of licence keys for this site from the options table
	 *
	 * @return array
	 */
	private function get_keys()
	{
		return get_option($this->validator_prefix.'licence_keys');
	}

	/**
	 * Displaying the error message in admin panel when plugin is activated without a valid licence key
	 */
	public function display_inactive_plugin_warning()
	{
		?>
		<div class="error">
			<p>
				<?php printf(__('The License key for <i><b>%s</b></i> is not valid . Please enter your <b>Licence Key</b> on the Jigoshop -> <a href="%s">Manage Licences</a> menu with your <b>Order email address</b>.  Until then, the plugin will not be enabled for use.', 'jigoshop'),
					$this->title,
					admin_url('admin.php?page=jigoshop-licence-validator'
				)); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Adding the Manage Licences menu to Jigoshop Menu
	 *
	 * @return boolean
	 */
	public function register_nav_menu_link()
	{
		// Don't register the Jigoshop Manage Licences submenu if it's already available.
		if ($this->submenu_exists()) {
			return false;
		}

		add_submenu_page(
			'jigoshop',
			__('Manage Licences', 'jigoshop'),
			__('Manage Licences', 'jigoshop'),
			'manage_jigoshop',
			$this->validator_token,
			array($this, 'admin_manage_licences')
		);
	}


	/**
	 * Method checks whether Jigoshop submenu option is already added by another extension
	 *
	 * @return boolean
	 */
	private function submenu_exists()
	{
		global $submenu;

		$exists = false;

		// Check if the menu item already exists.
		if (isset($submenu['jigoshop']) && is_array($submenu['jigoshop'])) {
			foreach ($submenu['jigoshop'] as $key => $value) {
				if (isset($value[2]) && ($value[2] == $this->validator_token)) {
					$exists = true;
					break;
				}
			}
		}

		return $exists;
	}

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param object $transient
	 * @return object $transient
	 */
	public function check_for_update($transient)
	{
		if (isset(self::$checked[$this->identifier])) {
			if (self::$checked[$this->identifier] !== false) {
				$transient->response[$this->plugin_slug] = self::$checked[$this->identifier];
			}

			return $transient;
		}

		$keys = $this->get_keys();
		$licence_key = isset($keys[$this->identifier]['licence_key'])	? $keys[$this->identifier]['licence_key']	: '';
		$licence_email = isset($keys[$this->identifier]['email'])	? $keys[$this->identifier]['email']	: '';

		// Get the remote version
		$response = $this->get_update_version($this->identifier, $licence_key, $licence_email);
		$obj = false;

		if (isset($response->version)) {
			if (isset($response->outdated_license) && $response->outdated_license == 1) {
				$this->display_incorrect_update_warning();
			}

			// If a newer version is available, add the update
			if (version_compare($this->version, $response->version, '<')) {
				$obj = new stdClass();
				$obj->slug = $this->file_slug;
				$obj->new_version = $response->version;
				$obj->url = $response->homepage;
				$obj->package = $response->update_url;

				$transient->response[$this->plugin_slug] = $obj;
			}
		}

		self::$checked[$this->identifier] = $obj;

		return $transient;
	}

	/**
	 * Plugin Version and update Information for a Jigoshop Licence API request
	 *
	 * @param string $product_id
	 * @param string $licence_key
	 * @param string $email
	 * @return boolean
	 */
	private function get_update_version($product_id, $licence_key, $email)
	{
		// POST data to send to the Jigoshop Licencing API
		$args = array(
			'email' => $email,
			'licence_key' => $licence_key,
			'product_id' => $product_id,
			'instance' => $this->generate_plugin_instance()
		);

		// Send request for detailed information
		return $this->prepare_request('update_version', $args);
	}

	/**
	 * Instance key for current WP installation
	 *
	 * @return string
	 */
	private function generate_plugin_instance()
	{
		return $_SERVER['SERVER_ADDR'].'@'.$_SERVER['HTTP_HOST'];
	}

	/**
	 * Prepare a request and send it to the Jigoshop Licence API on the selling shop
	 *
	 * @param string $action
	 * @param array $args
	 * @return boolean
	 */
	private function prepare_request($action, $args)
	{
		$request = wp_remote_post(
			$this->home_shop_url.'?licence-api='.$action, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $args,
				'cookies' => array(),
				'sslverify' => false,
			)
		);

		// Make sure the request was successful
		if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
			// Request failed
			return false;
		}

		// Read server response and return it
		return json_decode(wp_remote_retrieve_body($request));
	}

	/**
	 * Displaying the error message in admin panel when plugin license is outdated
	 */
	private function display_incorrect_update_warning()
	{
		?>
		<div class="error">
			<p>
				<?php echo sprintf(__('The License key for <i><b>%s</b></i> is outdated. Please renew your license. Until then, the update for this plugin will not be accessible.', 'jigoshop'), $this->title); ?>
			</p>
		</div>
	<?php
	}

	/**
	 * Get our self-hosted update description from the 'plugins_api' filter
	 *
	 * @param boolean $false
	 * @param array $action
	 * @param object $arg
	 * @return bool|object
	 */
	public function get_update_info($false, $action, $arg)
	{
		if ($action == 'plugin_information') {
			if ($arg->slug === $this->file_slug) {
				$keys = $this->get_keys();
				$licence_key = isset($keys[$this->identifier]['licence_key'])	? $keys[$this->identifier]['licence_key']	: '';
				$licence_email = isset($keys[$this->identifier]['email'])	? $keys[$this->identifier]['email']	: '';

				// Get the remote information
				$response = $this->get_update_version($this->identifier, $licence_key, $licence_email);

				$obj = new stdClass();
				$obj->name = $this->title;
				$obj->slug = $this->file_slug;
				$obj->homepage = $response->homepage;
				$obj->author_url = $response->author_url;
				$obj->version = $response->version;
				$obj->author = $response->author;
				$obj->url = $response->homepage;
				$obj->requires = $response->requires;
				$obj->tested = $response->tested;

				if (isset($response->sections)) {
					$converted = get_object_vars($response->sections);
					foreach ($converted as $index => $value) {
						$converted[$index] = html_entity_decode($value, ENT_COMPAT);
					}
					$obj->sections = $converted;
				} else {
					$obj->sections = array('changelog' => __('No update information available.', 'jigoshop'));
				}

				return $obj;
			}
		}

		return $false;
	}

	function in_plugin_update_message($plugin_data, $r)
	{
		if ($this->is_active()) {
			return;
		}

		$m = __('Please enter your license key on the <a href="%s">Manage Licences</a> page to enable automatic updates. You can buy your licence on <a href="%s">Jigoshop.com</a>.', 'jigoshop');
		echo '<br />' . sprintf($m, admin_url('admin.php?page=jigoshop-licence-validator'), $r->url);
	}

	/**
	 * Display Jigoshop Manage Licences page.
	 *
	 * @return void
	 */
	public function admin_manage_licences()
	{
		$user_email = $this->get_current_user_email();
		$messages = array();

		if (!empty($_POST[$this->validator_prefix.'nonce']) &&
			wp_verify_nonce($_POST[$this->validator_prefix.'nonce'], $this->validator_token.'-nonce')
		) {
			if (isset($_POST[$this->validator_token.'-login'])) {
				$messages = $this->save_licence_keys();
			}
		}

		// getting new keys after they were updated
		$keys = $this->get_keys();

		?>
		<div class="wrap">
			<h2><?php _e('Manage Jigoshop Digital Plugin Licences', 'jigoshop'); ?></h2>
			<?php foreach ($messages as $message) : ?>
				<div class="<?php echo($message['success'] ? 'updated below-h2' : 'error'); ?>">
					<p><?php echo $message['message']; ?></p>
				</div>
			<?php endforeach; ?>
			<p>
				<?php _e('To <em>activate</em> the licence, enter your licence keys and email addresses you used when you ordered the plugins.', 'jigoshop'); ?>
				<?php _e('<br />To <em>de-activate</em> the licence, remove the licence key, but leave the email address.', 'jigoshop'); ?>
			</p>

			<form name="<?php echo $this->validator_token; ?>-login" id="<?php echo $this->validator_token; ?>-login"
			      action="<?php echo admin_url('admin.php?page='.$this->validator_token); ?>" method="post">
				<?php wp_nonce_field($this->validator_token.'-nonce', $this->validator_prefix.'nonce'); ?>
				<fieldset>
					<label for="override_home_url"><?php _e('Override licensing validation URL for all extensions (by default it\'s defined in each extension)', 'jigoshop') ?> <input id="override_home_url" type="checkbox" name="override_home_url" <?php if($this->override_home_url) : echo 'checked="checked"'; endif; ?>></label>
					<div id="custom_url" style="display:none">
						<label for="custom_home_url"><?php _e('Custom validation URL', 'jigoshop'); ?> <input id="custom_home_url" type="text" name="custom_home_url" value="<?php echo $this->custom_home_url; ?>" placeholder="http://www.jigoshop.com/" style="width:338px;"> <?php _e('example: <i>http://jigoshop.com/</i> or <i>http://www.jigoshop.com/</i>', 'jigoshop'); ?></label>
					</div>
					<script>
						jQuery(function($){
							function changeVisibliltity(){
								if($('#override_home_url').is(':checked')){
									console.log('test1');
									$('#custom_url').show();
								}else{
									console.log('test2');
									$('#custom_url').hide();
								}
							}
							changeVisibliltity();
							$('#override_home_url').on('change', changeVisibliltity);
						});
					</script>
					<table class="form-table">
						<tbody>
						<?php foreach (self::$plugins as $plugin_identifier => $info) :
							$value = !empty($keys[$plugin_identifier]['licence_key']) ? $keys[$plugin_identifier]['licence_key'] : '';
							$email = !empty($keys[$plugin_identifier]['email']) ? $keys[$plugin_identifier]['email'] : '';
							if (!empty($_POST['licence_keys'][$plugin_identifier])) {
								$value = $_POST['licence_keys'][$plugin_identifier];
								$email = $_POST['licence_emails'][$plugin_identifier];
							} ?>
							<tr>
								<th scope="row"><label for="licence_key-<?php echo $plugin_identifier; ?>"><?php echo $info['title'] ?></label></th>
								<td>
									<input type="text" class="input-text input-licence regular-text" name="licence_keys[<?php echo $plugin_identifier; ?>]"
									       id="licence_key-<?php echo $plugin_identifier; ?>" value="<?php echo $value; ?>" />
								</td>
								<th scope="row" style="vertical-align: middle;">
									<label for="licence_key_email-<?php echo $plugin_identifier; ?>"><?php _e('Activation email', 'jigoshop') ?></label>
								</th>
								<td>
									<input type="email" class="input-text input-licence regular-text" placeholder="<?php echo $user_email; ?>" value="<?php echo $email; ?>"
									       name="licence_emails[<?php echo $plugin_identifier; ?>]" id="licence_key_email-<?php echo $plugin_identifier; ?>" />
								</td>
								<th>
									<?php if (!isset($keys[$plugin_identifier]['status']) || !$keys[$plugin_identifier]['status']) : ?>
										<b class="inactive-licence" style="color: #CC0000;"><?php _e('Licence is inactive!', 'jigoshop'); ?></b>
									<?php endif; ?>
								</th>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</fieldset>

				<fieldset>
					<p class="submit">
						<button type="submit" name="<?php echo $this->validator_token; ?>-login" id="<?php echo $this->validator_token; ?>-login" class="button-primary">
							<?php _e('Save', 'jigoshop'); ?>
						</button>
					</p>
				</fieldset>
			</form>
		</div>
	<?php
	}

	/**
	 * Gets the email address of the currently logged in user
	 *
	 * @return string
	 */
	private function get_current_user_email()
	{
		$current_user = wp_get_current_user();

		return $current_user->user_email;
	}

	/**
	 * Storing licence keys in database
	 *
	 * @return array
	 */
	private function save_licence_keys()
	{
		$messages = array();
		$keys = $this->get_keys();

		if(isset($_POST['override_home_url'])) {
			update_option('jigoshop_license_override_home_url', $_POST['override_home_url']);
			if(isset($_POST['custom_home_url']) && preg_match('/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\//i', $_POST['custom_home_url'])){
				update_option('jigoshop_license_custom_home_url', $_POST['custom_home_url']);
			} else {
				$messages[] = array(
					'success' => false,
					'message' => __('<b>Invalid custom license URL.</b>', 'jigoshop')
				);
			}
		} else {
			delete_option('jigoshop_license_override_home_url');
		}


		if (!isset($_POST['licence_keys'])) {
			return $messages;
		}

		foreach ($_POST['licence_keys'] as $product_id => $licence_key) {
			$licence_key = trim($licence_key);
			$activation_email = (isset($_POST['licence_emails'][$product_id]) && is_email($_POST['licence_emails'][$product_id])) ?
				$_POST['licence_emails'][$product_id] : $this->get_current_user_email();
			$licence_active = (isset($keys[$product_id]['status']) && $keys[$product_id]['status']);

			// Deactivate this key as it was removed
			if (empty($licence_key) && isset($keys[$product_id]['status']) && $keys[$product_id]['status'] && $licence_active) {
				$response = $this->deactivate($product_id, $keys[$product_id]['licence_key'], $activation_email);
				if ((isset($response->success) && $response->success) || (isset($response->code) && $response->code == 101)) {
					$messages[] = array(
						'success' => true,
						'message' => sprintf(__('<b>Key deactivated.</b> License key for <i>%s</i> has been <b>deactivated</b>.', 'jigoshop'), self::$plugins[$product_id]['title'])
					);
					// set status as inactive and remove licence from database
					$keys[$product_id] = array(
						'licence_key' => '',
						'status' => false,
						'email' => ''
					);
				} else {
					$messages[] = array(
						'success' => false,
						'message' => sprintf(__('%s deactivation: ', 'jigoshop'), self::$plugins[$product_id]['title']).$response->error
					);
				}
			} // Activate this key
			elseif (!$licence_active) {
				$response = $this->activate($product_id, $licence_key, $activation_email);
				if (isset($response->success) && $response->success) {
					$messages[] = array(
						'success' => true,
						'message' => sprintf(__('<b>Key activated.</b> License key for <i>%s</i> has been <b>activated</b>.', 'jigoshop'), self::$plugins[$product_id]['title'])
					);

					$keys[$product_id] = array(
						'licence_key' => $licence_key,
						'status' => true,
						'email' => (isset($_POST['licence_emails'][$product_id]) && is_email($_POST['licence_emails'][$product_id])) ?
							$_POST['licence_emails'][$product_id] : ''
					);
				} else {
					$messages[] = array(
						'success' => false,
						'message' => sprintf(__('%s activation: ', 'jigoshop'), self::$plugins[$product_id]['title']).$response->error
					);
				}
			}
		}

		$this->set_keys($keys);

		return $messages;
	}

	/**
	 * Deactivate Jigoshop Licence API request
	 *
	 * @param string $product_id
	 * @param string $licence_key
	 * @param string $email
	 * @return boolean
	 */
	private function deactivate($product_id, $licence_key, $email)
	{
		// POST data to send to the Jigoshop Licencing API
		$args = array(
			'email' => $email,
			'licence_key' => $licence_key,
			'product_id' => $product_id,
			'instance' => $this->generate_plugin_instance()
		);

		// Send request for detailed information
		return $this->prepare_request('deactivation', $args);
	}

	/**
	 * Activate Jigoshop Licence API request
	 *
	 * @param string $product_id
	 * @param string $licence_key
	 * @param string $email
	 * @return boolean
	 */
	private function activate($product_id, $licence_key, $email)
	{
		// POST data to send to the Jigoshop Licencing API
		$args = array(
			'email' => $email,
			'licence_key' => $licence_key,
			'product_id' => $product_id,
			'instance' => $this->generate_plugin_instance()
		);

		// Send request for detailed information
		return $this->prepare_request('activation', $args);
	}

	/**
	 * Saves a new set of licence keys in database options table
	 *
	 * @param array $keys
	 * @return boolean
	 */
	private function set_keys($keys)
	{
		return update_option($this->validator_prefix.'licence_keys', $keys);
	}

	/**
	 * Check for a valid licence Jigoshop Licence API request
	 *
	 * @param string $product_id
	 * @param string $licence_key
	 * @param string $email
	 * @return boolean
	 */
	private function check_licence($product_id, $licence_key, $email)
	{
		// POST data to send to the Jigoshop Licencing API
		$args = array(
			'email' => $email,
			'licence_key' => $licence_key,
			'product_id' => $product_id,
			'instance' => $this->generate_plugin_instance()
		);

		// Send request for detailed information
		return $this->prepare_request('check', $args);
	}
}
