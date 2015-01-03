<?php

namespace Jigoshop\Widget;

use Jigoshop\Core\Options;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Render;

class UserLogin extends \WP_Widget
{
	const ID = 'jigoshop_user_login';

	/** @var Options */
	private static $options;

	public function __construct()
	{
		$options = array(
			'classname' => self::ID,
			'description' => __('Displays a handy login form for users', 'jigoshop')
		);

		parent::__construct(self::ID, __('Jigoshop: Login', 'jigoshop'), $options);
	}

	public static function setOptions($options)
	{
		self::$options = $options;
	}

	/**
	 * Display the widget in the sidebar.
	 *
	 * @param array $args Sidebar arguments.
	 * @param array $instance The instance.
	 * @return bool|void
	 */
	public function widget($args, $instance)
	{
		$accountUrl = get_permalink(self::$options->getPageId(Pages::ACCOUNT));

		if (is_user_logged_in()) {
			global $current_user;

			$title = !empty($instance['title_user']) ? $instance['title_user'] : __('Hey %s!', 'jigoshop');
			$links = apply_filters('jigoshop_widget_logout_user_links', array(
				__('My Account', 'jigoshop') => $accountUrl,
				__('My Orders', 'jigoshop') => Api::getEndpointUrl('orders', '', $accountUrl),
				__('Change Password', 'jigoshop') => Api::getEndpointUrl('change-password', '', $accountUrl),
				__('Logout', 'jigoshop') => wp_logout_url(home_url()),
			));

			/** @noinspection PhpUndefinedFieldInspection */
			Render::output('widget/user_login/logged_in', array_merge($args, array(
				'title' => sprintf($title, ucwords($current_user->display_name)),
				'links' => $links,
			)));
		} else {
			// Print title
			$title = ($instance['title_guest']) ? $instance['title_guest'] : __('Login', 'jigoshop');
			$links = apply_filters('jigoshop_widget_login_user_links', array());
			$url = apply_filters('jigoshop_widget_login_redirect', $accountUrl);
			$loginUrl = wp_login_url($url);
			$passwordUrl = wp_lostpassword_url($url);
			// TODO: Support for other widgets
//			$fields = array();
//			// Support for other plugins which uses GET parameters
//			$fields = apply_filters('jigoshop_get_hidden_fields', $fields);

			Render::output('widget/user_login/log_in', array_merge($args, array(
				'title' => $title,
				'links' => $links,
				'loginUrl' => $loginUrl,
				'passwordUrl' => $passwordUrl,
			)));
		}
	}

	/**
	 * Update
	 * Handles the processing of information entered in the wordpress admin
	 * Flushes the cache & removes entry from options array
	 *
	 * @param  array  new instance
	 * @param  array  old instance
	 * @return  array  instance
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title_guest'] = strip_tags($new_instance['title_guest']);
		$instance['title_user'] = strip_tags($new_instance['title_user']);

		return $instance;
	}

	/**
	 * Form
	 * Displays the form for the wordpress admin
	 *
	 * @param  array  instance
	 * @return void
	 */
	public function form($instance)
	{
		// Get instance data
		$title_guest = isset($instance['title_guest']) ? esc_attr($instance['title_guest']) : null;
		$title_user = isset($instance['title_user']) ? esc_attr($instance['title_user']) : null;

		Render::output('widget/user_login/form', array(
			'title_guest_id' => $this->get_field_id('title_guest'),
			'title_guest_name' => $this->get_field_name('title_guest'),
			'title_guest' => $title_guest,
			'title_user_id' => $this->get_field_id('title_user'),
			'title_user_name' => $this->get_field_name('title_user'),
			'title_user' => $title_user,
		));
	}
}
