<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Licence;
use WPAL\Wordpress;

/**
 * Jigoshop licences admin page.
 *
 * @package Jigoshop\Admin
 */
class Licences implements PageInterface
{
	const NAME = 'jigoshop_licences';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Messages */
	private $messages;
	// TODO: Remove these variables
	private $validator_token = 'jigoshop-licence-validator';
	private $validator_prefix = 'jigoshop_licence_validator_';

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Licences::NAME) {
				return;
			}

//			Styles::add('jigoshop.admin.licences', JIGOSHOP_URL.'/assets/css/admin/settings.css');
		});
	}

	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Licences', 'jigoshop');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return Admin::MENU;
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'manage_jigoshop';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		$user_email = $this->get_current_user_email();
		$messages = array();

		// getting new keys after they were updated
		$keys = $this->getKeys();

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
			      action="<?php echo admin_url('admin.php?page='.self::NAME); ?>" method="post">
				<?php wp_nonce_field($this->validator_token.'-nonce', $this->validator_prefix.'nonce'); ?>
				<fieldset>
					<table class="form-table">
						<tbody>
						<?php foreach (Licence::__getPlugins() as $plugin_identifier => $info) :
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

		/** @noinspection PhpUndefinedFieldInspection */
		return $current_user->user_email;
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
}
