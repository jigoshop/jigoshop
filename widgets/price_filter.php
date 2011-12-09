<?php
/**
 * Price Filter Widget
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Widgets
 * @author		Jigowatt
 * @since		1.0
 * @copyright	Copyright (c) 2011 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */
class Jigoshop_Widget_Price_Filter extends WP_Widget {

	/**
	 * Constructor
	 * 
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct() {
		$options = array(
			'classname'		=> 'jigoshop_price_filter',
			'description'	=> __( 'Outputs a price filter slider', 'jigoshop' )
		);

		// Create the widget
		parent::__construct( 'jigoshop_price_filter', __( 'Jigoshop: Price Filter', 'jigoshop' ), $options );

		// Add price filter init to init hook
		add_action('init', array( &$this, 'jigoshop_price_filter_init') );
	}

	/**
	 * Widget
	 * 
	 * Display the widget in the sidebar
	 * Save output to the cache if empty
	 *
	 * @param	array	sidebar arguments
	 * @param	array	instance
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		if ( ! is_tax( 'product_cat' ) && ! is_post_type_archive( 'product' ) && ! is_tax( 'product_tag' ) )
			return false;

		global $_chosen_attributes, $wpdb, $all_post_ids;

		// Set the widget title
		$title = apply_filters(
			'widget_title', 
			( $instance['title'] ) ? $instance['title'] : __( 'Filter by Price', 'jigoshop' ), 
			$instance,
			$this->id_base
		);

		// Print the widget wrapper & title
		echo $before_widget;
		echo $before_title . $title . $after_title;

		// Remember current filters/search
		$fields = '';

		if ( get_search_query() ) {
			$fields .= '<input type="hidden" name="s" value="' . get_search_query() . '" />';
		}

		if ( isset( $_GET['post_type'] ) ) {
			$fields .= '<input type="hidden" name="post_type" value="' . esc_attr( $_GET['post_type'] ) . '" />';
		}

		if ( $_chosen_attributes ) foreach ( $_chosen_atributes as $attr => $val ) {
			$fields .= '<input type="hidden" name="'.str_replace('pa_', 'filter_', $attribute).'" value="'.implode(',', $value).'" />';
		}

		// Get maximum price
		$max = ceil($wpdb->get_var("SELECT max(meta_value + 0) 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE meta_key = 'price' AND (
			$wpdb->posts.ID IN (".implode(',', $all_post_ids).") 
			OR (
				$wpdb->posts.post_parent IN (".implode( ',', $all_post_ids ).")
				AND $wpdb->posts.post_parent != 0
			)
		)"));

		echo '<form method="get" action="' . esc_attr( $_SERVER['REQUEST_URI'] ) . '">
			<div class="price_slider_wrapper">
				<div class="price_slider"></div>
				<div class="price_slider_amount">
					<button type="submit" class="button">Filter</button>' . __( 'Price: ', 'jigoshop' ) . '<span></span>
					<input type="hidden" id="max_price" name="max_price" value="' . $max . '" />
					<input type="hidden" id="min_price" name="min_price" value="0" />
					' . $fields . '
				</div>
			</div>
		</form>';

		// Print closing widget wrapper
		echo $after_widget;
	}

	/**
	 * Update
	 * 
	 * Handles the processing of information entered in the wordpress admin
	 * Flushes the cache & removes entry from options array
	 *
	 * @param	array	new instance
	 * @param	array	old instance
	 * @return	array	instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function jigoshop_price_filter_init() {

		unset($_SESSION['min_price']);
		unset($_SESSION['max_price']);

		if ( isset( $_GET['min_price'] ) ) {	
			$_SESSION['min_price'] = $_GET['min_price'];
		}

		if ( isset( $_GET['max_price'] ) ) {
			$_SESSION['max_price'] = $_GET['max_price'];	
		}
	} 

	/**
	 * Form
	 * 
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	public function form( $instance ) {

		// Get instance data
		$title 	= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : null;

		// Widget Title
		echo "
		<p>
			<label for='{$this->get_field_id( 'title' )}'>" . __( 'Title:', 'jigoshop' ) . "</label>
			<input class='widefat' id='{$this->get_field_id( 'title' )}' name='{$this->get_field_name( 'title' )}' type='text' value='{$title}' />
		</p>";
	}
} // class Jigoshop_Widget_Price_Filter