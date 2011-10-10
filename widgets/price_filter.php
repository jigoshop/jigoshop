<?php
/**
 * Price Filter Widget
 * 
 * Generates a range slider to filter products by price
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Widgets
 * @author     Jigowatt
 * @since	   1.0
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
 
function jigoshop_price_filter_init() {
	
	unset($_SESSION['min_price']);
	unset($_SESSION['max_price']);
	
	if (isset($_GET['min_price'])) :
		
		$_SESSION['min_price'] = $_GET['min_price'];
		
	endif;
	if (isset($_GET['max_price'])) :
		
		$_SESSION['max_price'] = $_GET['max_price'];
		
	endif;
	
}

add_action('init', 'jigoshop_price_filter_init');

class Jigoshop_Widget_Price_Filter extends WP_Widget {

	/** constructor */
	function Jigoshop_Widget_Price_Filter() {
		$widget_ops = array( 'description' => __( "Shows a price filter slider in a widget which lets you narrow down the list of shown products in categories.", 'jigoshop') );
		parent::WP_Widget('price_filter', __('Jigoshop: Price Filter', 'jigoshop'), $widget_ops);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract($args);
		
		if (!is_tax( 'product_cat' ) && !is_post_type_archive('product') && !is_tax( 'product_tag' )) return;
		
		global $_chosen_attributes, $wpdb, $all_post_ids;
				
		$title = $instance['title'];
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		echo $before_widget . $before_title . $title . $after_title;
		
		// Remember current filters/search
		$fields = '';
		
		if (get_search_query()) $fields = '<input type="hidden" name="s" value="'.get_search_query().'" />';
		if (isset($_GET['post_type'])) $fields .= '<input type="hidden" name="post_type" value="'.$_GET['post_type'].'" />';
		
		if ($_chosen_attributes) foreach ($_chosen_attributes as $attribute => $value) :
		
			$fields .= '<input type="hidden" name="'.str_replace('pa_', 'filter_', $attribute).'" value="'.implode(',', $value).'" />';
		
		endforeach;
		
		$min = 0;
		
		$max = ceil($wpdb->get_var("SELECT max(meta_value + 0) 
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE meta_key = 'price' AND (
			$wpdb->posts.ID IN (".implode(',', $all_post_ids).") 
			OR (
				$wpdb->posts.post_parent IN (".implode(',', $all_post_ids).")
				AND $wpdb->posts.post_parent != 0
			)
		)"));
		
		echo '<form method="get" action="'.$_SERVER['REQUEST_URI'].'">
			<div class="price_slider_wrapper">
				<div class="price_slider"></div>
				<div class="price_slider_amount">
					<button type="submit" class="button">Filter</button>'.__('Price: ', 'jigoshop').'<span></span>
					<input type="hidden" id="max_price" name="max_price" value="'.$max.'" />
					<input type="hidden" id="min_price" name="min_price" value="'.$min.'" />
					'.$fields.'
				</div>
			</div>
		</form>';
		
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		if (!isset($new_instance['title']) || empty($new_instance['title'])) $new_instance['title'] = __('Filter by price', 'jigoshop');
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		global $wpdb;
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'jigoshop') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
		<?php
	}
} // class Jigoshop_Widget_Price_Filter