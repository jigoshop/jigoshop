<?php

namespace Jigoshop\Extension;

class CartFaviconCount
{
	public function __construct(){
		if(is_admin())
		{
			\Jigoshop_Base::get_options()->install_external_options_tab(__('Cart Favicon', 'jigoshop_cart_favicon_count'), $this->adminSettings());
			add_action('admin_enqueue_scripts', array($this, 'adminScripts'));
			add_action('admin_enqueue_scripts', array($this, 'adminStyles'));
		}
		if(\Jigoshop_Base::get_options()->get('jigoshop_cart_favicon_count_enable') == 'yes' && (\Jigoshop_Base::get_options()->exists('jigoshop_cart_favicon_count_url'))){
			add_action('wp_head', array($this, 'addFavicon'),1000);
			add_action('wp_enqueue_scripts', array($this, 'frontScripts'));
		}
	}

	public function adminSettings(){
		return array(
			array(
				'name' => __('Cart Favicon', 'jigoshop_cart_favicon_count'),
				'type' => 'title',
				'desc' => '',
				'id' => '',
			),
			array(
				'name' => __('Enable module', 'jigoshop_cart_favicon_count'),
				'type' => 'checkbox',
				'desc' => '',
				'id' => 'jigoshop_cart_favicon_count_enable',
				'choices' => array(
					'yes' => __('Yes', 'jigoshop_cart_favicon_count'),
					'no' => __('No', 'jigoshop_cart_favicon_count'),
				)
			),
			array(
				'name' => __('Upload Favicon', 'jigoshop_cart_favicon_count'),
				'id' => 'jigoshop_cart_favicon_count_url',
				'desc' => __('This module can not work properly if your site already has defined favicon.', 'jigoshop_web_optimization_system'),
				'type' => 'user_defined',
				'std' => '',
				'display' => array($this, 'displayFileUpload'),
				'update' => array($this, 'saveFileUpload')
			),
			array(
				'name' => __('Position', 'jigoshop_cart_favicon_count'),
				'type' => 'select',
				'desc' => '',
				'id' => 'jigoshop_cart_favicon_count_position',
				'choices' => array(
					'down' => __('Right down', 'jigoshop_cart_favicon_count'),
					'up' => __('Right up', 'jigoshop_cart_favicon_count'),
					'left' => __('Left down', 'jigoshop_cart_favicon_count'),
					'leftup' => __('Left up', 'jigoshop_cart_favicon_count'),
				)
			),
			array(
				'name' => __('Background Color', 'jigoshop_cart_favicon_count'),
				'id' => 'jigoshop_cart_favicon_count_bg_color',
				'desc' => '',
				'type' => 'text',
				'std' => '',
				'class' => 'picker'
			),
			array(
				'name' => __('Text Color', 'jigoshop_cart_favicon_count'),
				'id' => 'jigoshop_cart_favicon_count_text_color',
				'desc' => '',
				'type' => 'text',
				'std' => '',
				'class' => 'picker'
			),
		);
	}

	public function addFavicon() {
		$favicon = \Jigoshop_Base::get_options()->get('jigoshop_cart_favicon_count_url');
		echo '<link id="jigoshop_favicon" rel="shortcut icon" href="'.$favicon.'" />';
	}

	public function frontScripts() {
		jigoshop_add_script('favicon', JIGOSHOP_CART_FAVICON_COUNT_URL.'/assets/js/favicon.js', array('jquery'));
		jigoshop_localize_script('favicon', 'favicon_params', array(
			'favicon_count'	=> \jigoshop_cart::$cart_contents_count,
			'favicon_url' => \Jigoshop_Base::get_options()->get('jigoshop_cart_favicon_count_url'),
			'position' => \Jigoshop_Base::get_options()->get('jigoshop_cart_favicon_count_position'),
			'bg_color' =>  \Jigoshop_Base::get_options()->get('jigoshop_cart_favicon_count_bg_color'),
			'text_color' => \Jigoshop_Base::get_options()->get('jigoshop_cart_favicon_count_text_color')
		));
	}

	public function adminScripts() {
		jigoshop_add_script('favicon', JIGOSHOP_CART_FAVICON_COUNT_URL.'/vendor/js/colpick.js', array('jquery'));
		jigoshop_add_script('colpick', JIGOSHOP_CART_FAVICON_COUNT_URL.'/assets/js/init-colpick.js', array('jquery'));
	}

	public function adminStyles() {
		jigoshop_add_style('favicon', JIGOSHOP_CART_FAVICON_COUNT_URL.'/vendor/css/colpick.css');
		jigoshop_add_style('colpick', JIGOSHOP_CART_FAVICON_COUNT_URL.'/assets/css/init-colpick.css');
	}

	public function displayFileUpload() {
		ob_start();
		echo '<table>';
		if(\Jigoshop_Base::get_options()->exists('jigoshop_cart_favicon_count_url')){
			echo '<tr><td>'.__('Actual icon:', 'jigoshop_cart_favicon_count').'</td><td><img src="'.\Jigoshop_Base::get_options()->get('jigoshop_cart_favicon_count_url').'"/></td></tr>';
		}
		echo '<tr><td>'.__('Upload new icon:', 'jigoshop_cart_favicon_count').'</td><td><input type="file" id="jigoshop_cart_favicon_count_file" name="jigoshop_cart_favicon_count_file" value="" /></td></tr>';
		echo '</table>';
		return ob_get_clean();
	}

	public function saveFileUpload() {
		if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$uploadedfile = $_FILES['jigoshop_cart_favicon_count_file'];
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
		if ( $movefile ) {
			return $movefile['url'];
		}
	}
}