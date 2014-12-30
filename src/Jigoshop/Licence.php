<?php

namespace Jigoshop;

/**
 * Jigoshop licence validator.
 *
 * TODO: Properly re-implement!
 *
 * @package Jigoshop
 */
class Licence
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

//		if (is_ssl()) { // TODO: It should be enabled with proper options (i.e. require secure connection).
			$this->home_shop_url = str_replace('http://', 'https://', $this->home_shop_url);
//		}
		if ($this->home_shop_url[strlen($this->home_shop_url)-1] !== '/') {
			$this->home_shop_url .= '/';
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
			// Define the alternative response for information checking
			add_filter('plugins_api', array($this, 'getUpdateData'), 20, 3);

			if (!empty($_POST[$this->validator_prefix.'nonce']) &&
				wp_verify_nonce($_POST[$this->validator_prefix.'nonce'], $this->validator_token.'-nonce')
			) {
				if (isset($_POST[$this->validator_token.'-login'])) {
					$messages = $this->save_licence_keys();
				}
			}
		}

		// define the alternative API for updating checking
		add_filter('pre_set_site_transient_update_plugins', array($this, 'checkUpdates'));
		add_action('in_plugin_update_message-'.$this->plugin_slug, array($this, 'updateMessage'), 10, 2);
	}

	public static function __getPlugins()
	{
		return self::$plugins;
	}

	/**
	 * Is Licence Active for this plugin
	 * All plugins should call this early in their existance to check if their licence is valid
	 * Allow plugin to function normally if it is and limit or disable functionality otherwise
	 *
	 * @return boolean
	 */
	public function isActive()
	{
		$active = $this->is_active();

		if (!$active) {
			add_action('admin_notices', array($this, 'display_inactive_plugin_warning'));
		}

		return $active;
	}

	private function is_active()
	{
		$keys = $this->getKeys();
		return (isset($keys[$this->identifier]['status']) && $keys[$this->identifier]['status']);
	}

	/**
	 * Returns a set of licence keys for this site from the options table
	 *
	 * @return array
	 */
	private function getKeys()
	{
		return get_option($this->validator_prefix.'licence_keys');
	}

	/**
	 * Displaying the error message in admin panel when plugin is activated without a valid licence key
	 */
	public function displayWarnings()
	{
		?>
		<div class="error">
			<p>
				<?php echo sprintf(__('The License key for <i><b>%s</b></i> is not valid . Please enter your <b>Licence Key</b> on the Jigoshop->Manage Licences Menu with your <b>Order email address</b>.  Until then, the plugin will not be enabled for use.', 'jigoshop'), $this->title); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param object $transient
	 * @return object $transient
	 */
	public function checkUpdates($transient)
	{
		if (isset(self::$checked[$this->identifier])) {
			if (self::$checked[$this->identifier] !== false) {
				$transient->response[$this->plugin_slug] = self::$checked[$this->identifier];
			}

			return $transient;
		}

		$keys = $this->getKeys();
		$licence_key = isset($keys[$this->identifier]['licence_key'])	? $keys[$this->identifier]['licence_key']	: '';
		$licence_email = isset($keys[$this->identifier]['email'])	? $keys[$this->identifier]['email']	: '';

		// Get the remote version
		$response = $this->getUpdateVersion($this->identifier, $licence_key, $licence_email);
		$obj = false;

		if (isset($response->version)) {
			if (isset($response->outdated_license) && $response->outdated_license == 1) {
				$this->display_incorrect_update_warning();
			}

			// If a newer version is available, add the update
			if (version_compare($this->version, $response->version, '<')) {
				$obj = new \stdClass();
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
	private function getUpdateVersion($product_id, $licence_key, $email)
	{
		// POST data to send to the Jigoshop Licencing API
		$args = array(
			'email' => $email,
			'licence_key' => $licence_key,
			'product_id' => $product_id,
			'instance' => $this->generatePluginInstance()
		);

		// Send request for detailed information
		return $this->prepare_request('update_version', $args);
	}

	/**
	 * Instance key for current WP installation
	 *
	 * @return string
	 */
	private function generatePluginInstance()
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
	public function getUpdateData($false, $action, $arg)
	{
		if ($action == 'plugin_information') {
			if ($arg->slug === $this->file_slug) {
				$keys = $this->getKeys();
				$licence_key = isset($keys[$this->identifier]['licence_key'])	? $keys[$this->identifier]['licence_key']	: '';
				$licence_email = isset($keys[$this->identifier]['email'])	? $keys[$this->identifier]['email']	: '';

				// Get the remote information
				$response = $this->getUpdateVersion($this->identifier, $licence_key, $licence_email);

				$obj = new \stdClass();
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

	public function updateMessage($plugin_data, $r)
	{
		if ($this->is_active()) {
			return;
		}

		$m = __('Please enter your license key on the <a href="%s">Manage Licences</a> page to enable automatic updates. You can buy your licence on <a href="%s">Jigoshop.com</a>.', 'jigoshop');
		echo '<br />' . sprintf($m, admin_url('admin.php?page=jigoshop-licence-validator'), $r->url);
	}

	/**
	 * Gets the email address of the currently logged in user
	 *
	 * @return string
	 */
	private function get_current_user_email()
	{
		$current_user = wp_get_current_user();

		/** @noinspection PhpUndefinedFieldInspection */
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
		$keys = $this->getKeys();

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
				if (isset($response->success) && $response->success) {
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
			'instance' => $this->generatePluginInstance()
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
			'instance' => $this->generatePluginInstance()
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
}
