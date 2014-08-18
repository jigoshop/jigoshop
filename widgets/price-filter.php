<?php

/**
 * Price Filter Widget
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
class Jigoshop_Widget_Price_Filter extends WP_Widget
{
	/**
	 * Constructor
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct()
	{
		$options = array(
			'classname' => 'jigoshop_price_filter',
			'description' => __('Outputs a price filter slider', 'jigoshop')
		);

		// Create the widget
		parent::__construct('jigoshop_price_filter', __('Jigoshop: Price Filter', 'jigoshop'), $options);

		// Add price filter init to init hook
		add_action('init', array($this, 'jigoshop_price_filter_init'));

		// Add own hidden fields to filter
		add_filter('jigoshop_get_hidden_fields', array($this, 'jigoshop_price_filter_hidden_fields'));

	}

	public function jigoshop_price_filter_hidden_fields($fields)
	{
		if (isset($_GET['max_price'])) {
			$fields['max_price'] = $_GET['max_price'];
		}

		if (isset($_GET['min_price'])) {
			$fields['min_price'] = $_GET['min_price'];
		}

		return $fields;
	}

	/**
	 * Widget
	 * Display the widget in the sidebar
	 * Save output to the cache if empty
	 *
	 * @param  array $args sidebar arguments
	 * @param  array $instance instance
	 */
	public function widget($args, $instance)
	{
		extract($args);

		if (!is_tax('product_cat') && !is_post_type_archive('product') && !is_tax('product_tag')) {
			return;
		}

		global $_chosen_attributes, $wpdb, $jigoshop_all_post_ids_in_view;

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Filter by Price', 'jigoshop'),
			$instance,
			$this->id_base
		);

		// Print the widget wrapper & title
		echo $before_widget;
		if ($title) {
			echo $before_title.$title.$after_title;
		}

		// Remember current filters/search
		$fields = array();

		// Support for other plugins which uses GET parameters
		$fields = apply_filters('jigoshop_get_hidden_fields', $fields);

		if (get_search_query()) {
			$fields['s'] = get_search_query();
		}

		if (isset($_GET['post_type'])) {
			$fields['post_type'] = esc_attr($_GET['post_type']);
		}

		if (!empty($_chosen_attributes)) {
			foreach ($_chosen_attributes as $attribute => $value) {
				$fields[str_replace('pa_', 'filter_', $attribute)] = implode(',', $value);
			}
		}

		// Get maximum price
		// @todo: Currently we can only handle regular price, looks like we may need to implement the price meta field after all :(
		$max = ceil($wpdb->get_var("SELECT max(meta_value + 0)
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE meta_key = 'regular_price' AND (
			$wpdb->posts.ID IN (".implode(',', $jigoshop_all_post_ids_in_view).")
			OR (
				$wpdb->posts.post_parent IN (".implode(',', $jigoshop_all_post_ids_in_view).")
				AND $wpdb->posts.post_parent != 0
			)
		)"));

		echo '<form method="get" action="'.esc_attr($_SERVER['REQUEST_URI']).'">
			<div class="price_slider_wrapper">
				<div class="price_slider"></div>
				<div class="price_slider_amount">
					<button type="submit" class="button">'.__('Filter', 'jigoshop').'</button>'.__('Price: ', 'jigoshop').'<span></span>
					<input type="hidden" id="max_price" name="max_price" value="'.esc_attr($max).'" />
					<input type="hidden" id="min_price" name="min_price" value="0" />
					';
		foreach ($fields as $key => $value) {
			if (!in_array($key, array('max_price', 'min_price'))) {
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
		}
		echo '
				</div>
				<div class="clear"></div>
			</div>
		</form>';

		?>
		<script type="text/javascript">
			/*<![CDATA[*/
			jQuery(document).ready(function($){
				// Price slider
				var min_price = parseInt($('.price_slider_amount #min_price').val());
				var max_price = parseInt($('.price_slider_amount #max_price').val());
				var current_min_price, current_max_price;
				if(jigoshop_params.min_price){
					current_min_price = jigoshop_params.min_price;
				} else {
					current_min_price = min_price;
				}
				if(jigoshop_params.max_price){
					current_max_price = jigoshop_params.max_price;
				} else {
					current_max_price = max_price;
				}
				$('.price_slider').slider({
					range: true,
					min: min_price,
					max: max_price,
					values: [current_min_price, current_max_price],
					create: function(event, ui){
						$(".price_slider_amount span").html(jigoshop_params.currency_symbol + current_min_price + " - " + jigoshop_params.currency_symbol + current_max_price);
						$(".price_slider_amount #min_price").val(current_min_price);
						$(".price_slider_amount #max_price").val(current_max_price);
					},
					slide: function(event, ui){
						$(".price_slider_amount span").html(jigoshop_params.currency_symbol + ui.values[0] + " - " + jigoshop_params.currency_symbol + ui.values[1]);
						$("input#min_price").val(ui.values[0]);
						$("input#max_price").val(ui.values[1]);
					}
				});
			});
			/*]]>*/
		</script>
		<?php
		// Print closing widget wrapper
		echo $after_widget;
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
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	public function jigoshop_price_filter_init()
	{
		// if price filter in use on front end, load jquery-ui slider (WP loads in footer)
		if (is_active_widget(false, false, 'jigoshop_price_filter', true) && !is_admin()) {
			wp_enqueue_script('jquery-ui-slider');
		}

		unset(jigoshop_session::instance()->min_price);
		unset(jigoshop_session::instance()->max_price);

		if (isset($_GET['min_price'])) {
			jigoshop_session::instance()->min_price = $_GET['min_price'];
		}

		if (isset($_GET['max_price'])) {
			jigoshop_session::instance()->max_price = $_GET['max_price'];
		}
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

		// Widget Title
		echo "
		<p>
			<label for='{$this->get_field_id('title')}'>".__('Title:', 'jigoshop')."</label>
			<input class='widefat' id='{$this->get_field_id('title')}' name='{$this->get_field_name('title')}' type='text' value='{$title}' />
		</p>";
	}
} // class Jigoshop_Widget_Price_Filter

function jigoshop_price_filter($filtered_posts)
{
	if (isset($_GET['max_price']) && isset($_GET['min_price'])) {
		$matched_products = array(0);
		$query = new WP_Query(array(
			'fields' => 'ids',
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'regular_price',
					'value' => array($_GET['min_price'], $_GET['max_price']),
					'type' => 'NUMERIC',
					'compare' => 'BETWEEN'
				)
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => array('grouped', 'variable'),
					'operator' => 'NOT IN'
				)
			)
		));
		$products = $query->get_posts();

		$query = new WP_Query(array(
			'fields' => 'ids',
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => array('variable', 'grouped'),
					'relation' => 'OR',
				)
			)
		));
		$parents = $query->get_posts();

		$query = new WP_Query(array(
			'post_type' => array('product', 'product_variation'),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'post_parent__in' => $parents,
			'meta_query' => array(
				array(
					'key' => 'regular_price',
					'value' => array($_GET['min_price'], $_GET['max_price']),
					'type' => 'NUMERIC',
					'compare' => 'BETWEEN'
				)
			),
		));
		$children = $query->get_posts();

		$products[] = 0;
		foreach($children as $child){
			if(!in_array($child->post_parent, $products)){
				$products[] = $child->post_parent;
			}
		}

		$filtered_posts = array_intersect($filtered_posts, $products);
	}

	return $filtered_posts;
}

add_filter('loop-shop-posts-in', 'jigoshop_price_filter');
