<?php
?>
<?php
// Grouped Products
// TODO: Needs refactoring & a bit of love
$posts_in = (array)get_objects_in_term(get_term_by('slug', 'grouped', 'product_type')->term_id, 'product_type');
$posts_in = array_unique($posts_in);

if ((bool)$posts_in) {

	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby' => 'title',
		'order' => 'asc',
		'post_parent' => 0,
		'include' => $posts_in,
	);

	$grouped_products = get_posts($args);

	$options = array(null => '&ndash; Pick a Product Group &ndash;');

	if ($grouped_products) {
		foreach ($grouped_products as $product) {
			if ($product->ID == $post->ID) {
				continue;
			}

			$options[$product->ID] = $product->post_title;
		}
	}
	// Only echo the form if we have grouped products
	$args = array(
		'id' => 'parent_id',
		'label' => __('Product Group', 'jigoshop'),
		'options' => $options,
		'selected' => $post->post_parent,
	);
	echo Jigoshop_Forms::select($args);
}

// Ordering
$args = array(
	'id' => 'menu_order',
	'label' => __('Sort Order', 'jigoshop'),
	'type' => 'number',
	'value' => $post->menu_order,
);
echo Jigoshop_Forms::input($args);