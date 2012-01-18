<?php
/**
 * Product Search Widget
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
 
class Jigoshop_Widget_Product_Categories extends WP_Widget {

	/**
	 * Constructor
	 * 
	 * Setup the widget with the available options
	 */
	public function __construct() {
	
		$options = array(
			'classname' => 'widget_product_categories',
			'description' => __( "A list or dropdown of product categories", 'jigoshop' ),
		);
		
		// Create the widget
		parent::__construct('product_categories', __('Jigoshop: Product Categories', 'jigoshop'), $options);
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
	
		// Extract the widget arguments
		extract( $args );

		// Set the widget title
		$title = ( ! empty($instance['title']) ) ? $instance['title'] : __('Product Categories', 'jigoshop');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		// Get options
		$count			= (bool) isset($instance['count']) ? $instance['count'] : false;
		$is_hierarchial = (bool) isset($instance['hierarchical']) ? $instance['hierarchical'] : false;
		$is_dropdown	= (bool) isset($instance['dropdown']) ? $instance['dropdown'] : false;

		// Print the widget wrapper & title
		echo $before_widget;
		echo $before_title . $title . $after_title;

		// Define options for the list
		$args = array(
			'orderby'		=> 'name',
			'show_count'	=> $count,
			'hierarchical'	=> $is_hierarchial,
			'taxonomy'		=> 'product_cat',
			'title_li'		=> null,
		);

		// Output as dropdown or unordered list
		if( $is_dropdown ) {
		
			// Set up arguements
			unset($args['title_li']);
			$args['name'] = 'dropdown_product_cat';
			
			// Print dropdown
			// wp_dropdown_categories($args); Commented out due to wordpress bug 13258 not supporting custom taxonomies
			// See: http://core.trac.wordpress.org/ticket/13258
			
			$terms = get_terms('product_cat');
			$output = "<select name='product_cat' id='dropdown_product_cat'>";
			// TODO: Be better to make this all products link
			$output .= '<option value="">'.__('Select Category', 'jigoshop').'</option>';
			foreach($terms as $term){
				$root_url = get_bloginfo('url');
				$term_taxonomy=$term->taxonomy;
				$term_slug=$term->slug;
				$term_name =$term->name;
				$link = $term_slug;
				$selected = (strpos($_SERVER['REQUEST_URI'], $term_slug)) ? 'selected' : null;
				
				$output .='<option value="'.$link.'" ' . $selected . '>'.$term_name.'</option>';
			}
			$output .="</select>";
			echo $output;
			
			// TODO: Move this javascript to its own file (plugins.js?)
		?>
			<script type='text/javascript'>
			/* <![CDATA[ */
				var dropdown = document.getElementById("dropdown_product_cat");
				function onCatChange() {
					if ( dropdown.options[dropdown.selectedIndex].value !=='' ) {
						location.href = "<?php echo home_url(); ?>/?product_cat="+dropdown.options[dropdown.selectedIndex].value;
					}
				}
				dropdown.onchange = onCatChange;
			/* ]]> */
			</script>
		<?php	
		} else {
		
			// Print list of categories
			echo '<ul>';
			wp_list_categories(apply_filters('widget_product_categories_args', $args));
			echo '</ul>';
		}
		
		// Print closing widget wrapper
		echo $after_widget;
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
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		// Update values
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}
	
	/**
	 * Form
	 * 
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	function form( $instance ) {
		
		// Get values from instance
		$title			= (isset($instance['title'])) ? esc_attr($instance['title']) : null;
		$count			= (bool) isset($instance['count']) ? $instance['count'] : false;
		$hierarchical	= (bool) isset($instance['hierarchical']) ? $instance['hierarchical'] : false;
		$dropdown		= (bool) isset($instance['dropdown']) ? $instance['dropdown'] : false;
		
		// Widget title
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e('Title:', 'jigoshop'); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo $title; ?>" />
		</p>
		<?php // As a dropdown ?>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('dropdown') ); ?>" name="<?php echo esc_attr( $this->get_field_name('dropdown') ); ?>" <?php checked( $dropdown ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id('dropdown') ); ?>"><?php _e( 'Show as dropdown', 'jigoshop' ); ?></label>
		</p>
		<?php // Show product count ?>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('count') ); ?>" name="<?php echo esc_attr( $this->get_field_name('count') ); ?>" <?php checked( $count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id('count') ); ?>"><?php _e( 'Show product counts', 'jigoshop' ); ?></label>
		</p>
		<?php // Is hierarchical ?>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('hierarchical') ); ?>" name="<?php echo esc_attr( $this->get_field_name('hierarchical') ); ?>" <?php checked( $hierarchical ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id('hierarchical') ); ?>"><?php _e( 'Show hierarchy', 'jigoshop' ); ?></label>
		</p>
		<?php
	}

} // class Jigoshop_Widget_Product_Categories