<?php
/**
 * Tag Cloud Widget
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Widgets
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Widget_Tag_Cloud extends WP_Widget {

	/**
	 * Constructor
	 *
	 * Setup the widget with the available options
	 */
	public function __construct() {

		$options = array(
			'description' => __( "Your most used product tags in cloud format", 'jigoshop'),
		);

		// Create the widget
		parent::__construct('product_tag_cloud', __('Jigoshop: Product Tag Cloud', 'jigoshop'), $options);
	}

	/**
	 * Widget
	 *
	 * Display the widget in the sidebar
	 *
	 * @param	array	sidebar arguments
	 * @param	array	instance
	 */
	public function widget( $args, $instance ) {

		// Get the widget cache from the transient
		$cache = get_transient( 'jigoshop_widget_cache' );
		// If this tag cloud widget instance is cached, get from the cache
		if ( isset( $cache[$this->id] ) ) {
			echo $cache[$this->id];
			return false;
		}

		// Otherwise Start buffering and output the Widget
		ob_start();

		// Extract the widget arguments
		extract($args);

		// Set the widget title
		$title = ( ! empty($instance['title']) ) ? $instance['title'] : __('Product Tags', 'jigoshop');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);

		// Print the widget wrapper & title
		echo $before_widget;
		echo $before_title . $title . $after_title;

		// Print tag cloud with wrapper
		echo '<div class="tagcloud">';
		wp_tag_cloud( apply_filters('widget_tag_cloud_args', array('taxonomy' => 'product_tag') ) );
		echo "</div>\n";

		// Print closing widget wrapper
		echo $after_widget;

		// Flush output buffer and save to transient cache
		$result = ob_get_flush();
		$cache[$this->id] = $result;
		set_transient( 'jigoshop_widget_cache', $cache, 3600*3 ); // 3 hours ahead
	}

	/**
	 * Update
	 *
	 * Handles the processing of information entered in the wordpress admin
	 *
	 * @param	array	new instance
	 * @param	array	old instance
	 * @return	array	instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Save new values
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['taxonomy'] = stripslashes(isset($new_instance['taxonomy']) ? $new_instance['taxonomy'] : '');

		return $instance;
	}

	/**
	 * Form
	 *
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	public function form( $instance ) {
		$title = (isset($instance['title'])) ? esc_attr($instance['title']) : null;

		// Widget title
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e('Title:', 'jigoshop'); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

} // class Jigoshop_Widget_Tag_Cloud