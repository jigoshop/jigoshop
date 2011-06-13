<?php
/**
 * Product Type
 * 
 * Function for displaying the product type meta (specific) meta boxes
 *
 * @author 		Jigowatt
 * @category 	Admin Write Panels
 * @package 	JigoShop
 */

foreach(glob( dirname(__FILE__)."/product-types/*.php" ) as $filename) include_once($filename);

/**
 * Product type meta box
 * 
 * Display the product type meta box which contains a hook for product types to hook into and show their options
 *
 * @since 		1.0
 */
function jigoshop_product_type_options_box() {

	global $post;
	?>
	<div id="simple_product_options" class="panel jigoshop_options_panel">
		<?php
			// List Grouped products
			$posts_in = (array) get_objects_in_term( get_term_by( 'slug', 'grouped', 'product_type' )->term_id, 'product_type' );
			$posts_in = array_unique($posts_in);
			
			$field = array( 'id' => 'parent_id', 'label' => 'Parent post' );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].'</label><select id="'.$field['id'].'" name="'.$field['id'].'"><option value="">'.__('Choose a grouped product&hellip;', 'jigoshop').'</option>';

			if (sizeof($posts_in)>0) :
				$args = array(
					'post_type'	=> 'product',
					'post_status' => 'publish',
					'numberposts' => -1,
					'orderby' => 'title',
					'order' => 'asc',
					'post_parent' => 0,
					'include' => $posts_in,
				);
				$grouped_products = get_posts($args);
				$loop = 0;
				if ($grouped_products) : foreach ($grouped_products as $product) :
					
					if ($product->ID==$post->ID) continue;
					
					echo '<option value="'.$product->ID.'" ';
					if ($post->post_parent==$product->ID) echo 'selected="selected"';
					echo '>'.$product->post_title.'</option>';
			
				endforeach; endif; 
			endif;

			echo '</select></p>';
			
			// Ordering
			$menu_order = $post->menu_order;
			$field = array( 'id' => 'menu_order', 'label' => 'Order:' );
			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].'</label>
				<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$menu_order.'" /></p>';

		?>
	</div>
	<?php 
	do_action('jigoshop_product_type_options_box');
}