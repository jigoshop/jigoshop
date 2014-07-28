<?php

/**
 * Product Search Widget
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Widgets
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
class Jigoshop_Widget_Product_Search extends WP_Widget
{
	/**
	 * Constructor
	 * Setup the widget with the available options
	 */
	public function __construct()
	{
		$options = array(
			'classname' => 'jigoshop_product_search',
			'description' => __('A search form for your products', 'jigoshop')
		);

		// Create the widget
		parent::__construct('jigoshop_product_search', __('Jigoshop: Product Search', 'jigoshop'), $options);

		// Add own hidden fields to filter
		add_filter('jigoshop_get_hidden_fields', array($this, 'jigoshop_price_filter_hidden_fields'));

	}

	public function jigoshop_price_filter_hidden_fields($fields)
	{
		if (isset($_GET['s'])) {
			$fields['s'] = $_GET['s'];
		}

		if (isset($_GET['post_type'])) {
			$fields['post_type'] = $_GET['post_type'];
		}

		return $fields;
	}

	/**
	 * Widget
	 * Display the widget in the sidebar
	 *
	 * @param  array  sidebar arguments
	 * @param  array  instance
	 */
	public function widget($args, $instance)
	{
		// Extract the widget arguments
		extract($args);

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Product Search', 'jigoshop'),
			$instance,
			$this->id_base
		);

		// Print the widget wrapper & title
		echo $before_widget;
		if ($title) {
			echo $before_title.$title.$after_title;
		}

		$fields = array();
		// Support for other plugins which uses GET parameters
		$fields = apply_filters('jigoshop_get_hidden_fields', $fields);

		// Construct the form
		$form = '<form role="search" method="get" id="searchform" action="'.home_url().'">';
		foreach ($fields as $key => $value) {
			if (!in_array($key, array('s', 'post_type'))) {
				$form .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
		}
		$form .= '<div>';
		$form .= '<label class="assistive-text" for="s">'.__('Search for:', 'jigoshop').'</label>';
		$form .= '<input type="text" value="'.get_search_query().'" name="s" id="s" placeholder="'.__('Search for products', 'jigoshop').'" />';
		$form .= '<input type="submit" id="searchsubmit" value="'.__('Search', 'jigoshop').'" />';
		$form .= '<input type="hidden" name="post_type" value="product" />';
		$form .= '</div>';
		$form .= '</form>';

		// Apply a filter to allow for additional fields
		echo apply_filters('jigoshop_product_search_form', $form, $instance, $this->id_base);

		// Print closing widget wrapper
		echo $after_widget;
	}

	/**
	 * Update
	 * Handles the processing of information entered in the wordpress admin
	 *
	 * @param  array  new instance
	 * @param  array  old instance
	 * @return  array  instance
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags($new_instance['title']);

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
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;

		// Widget title
		echo "
		<p>
			<label for='{$this->get_field_id('title')}'>".__('Title:', 'jigoshop')."</label>
			<input class='widefat' id='{$this->get_field_id('title')}' name='{$this->get_field_name('title')}' type='text' value='{$title}' />
		</p>";
	}
}
