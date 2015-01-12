<?php

class jigoshop_emails extends Jigoshop_Base
{
	private static $mail_list = array();
	private static $call_next_action = true;

	public static function suppress_next_action()
	{
		self::$call_next_action = false;
	}

	private static function can_call_next_action()
	{
		if(self::$call_next_action == false){
			self::$call_next_action = true;

			return false;
		}

		return true;
	}

	public static function get_mail_list()
	{
		return self::$mail_list;
	}

	public static function set_actions($post_id, $hooks)
	{
		$allowed_templates = self::get_options()->get('jigoshop_emails');
		if(isset($allowed_templates) && is_array($allowed_templates)) {
			$allowed_templates = array_map(function ($arg) use ($post_id){
				return array_filter($arg, function ($arg_2) use ($post_id){
					return $arg_2 != $post_id;
				});
			}, $allowed_templates);
		}

		foreach ($hooks as $hook) {
			$allowed_templates[$hook][] = $post_id;
		}
		self::get_options()->set('jigoshop_emails', $allowed_templates);
	}

	public static function register_mail($hook, $description, array $accepted_args)
	{
		self::$mail_list[$hook] = array(
			'description' => $description,
			'accepted_args' => $accepted_args
		);
	}

	public static function send_mail($hook, array $args = array(), $to)
	{
		if(self::can_call_next_action() == false){
			return;
		}

		$allowed_templates = self::get_options()->get('jigoshop_emails');
		if (!$allowed_templates[$hook]) {
			return;
		}

		foreach ($allowed_templates[$hook] as $post_id) {
			$post = get_post($post_id);
			if (!empty($post) && $post->post_status == 'publish') {
				$subject = get_post_meta($post_id, 'jigoshop_email_subject', true);
				$post->post_title = empty($subject) ? $post->post_title : $subject;
				$post = self::filter_post($post, $args);
				$headers = array(
					'MIME-Version: 1.0',
					'Content-Type: text/html; charset=UTF-8',
					'From: "'.self::get_options()->get('jigoshop_email_from_name').'" <'.self::get_options()->get('jigoshop_email').'>',
				);
				$post->post_content = self::get_options()->get('jigoshop_email_footer') ? $post->post_content.'<br/><br/>'.self::get_options()->get('jigoshop_email_footer') : $post->post_content;

				wp_mail($to,
					$post->post_title,
					nl2br($post->post_content),
					$headers
				);
			}
		}
	}

	private static function filter_post(wp_post $post, array $args)
	{
		if (empty($args)) {
			return $post;
		}
		foreach ($args as $key => $value) {
			$post->post_title = str_replace('['.$key.']', $value, $post->post_title);
			if(empty($value)){
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$2', $post->post_content);
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[\/'.$key.'\]#si', '', $post->post_content);
				$post->post_content = str_replace('['.$key.']', '', $post->post_content);
			} else {
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[value\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$1'.'['.$key.']'.'$2', $post->post_content);
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$1', $post->post_content);
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[value\](.*?)\[\/'.$key.'\]#si', '$1'.'['.$key.']'.'$2', $post->post_content);
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[\/'.$key.'\]#si', '$1', $post->post_content);
				$post->post_content = str_replace('['.$key.']', $value, $post->post_content);
			}
		}
		return $post;
	}
}

add_action('load-jigoshop_page_jigoshop_settings', function(){
	if(isset($_GET['install_emails'])){
		do_action('jigoshop_install_emails');
		add_settings_error( '', 'settings_updated', __( 'Default emails generated.' , 'jigoshop' ), 'updated' );
	}
});
