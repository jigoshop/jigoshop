<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * @var $addons stdClass Object containing data for extension list.
 */

$categories = (array)$addons->categories;
$view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : reset(array_keys($categories));
$theme = wp_get_theme();
?>
<div class="wrap jigoshop jigoshop_extensions_wrap">
	<div class="icon32 icon32-posts-product" id="icon-jigoshop"><br /></div>
	<h2>
		<?php _e('Jigoshop Add-ons/Extensions', 'jigoshop'); ?>
		<a href="https://www.jigoshop.com/product-category/extensions/" class="add-new-h2"><?php _e('Browse all extensions', 'jigoshop'); ?></a>
	</h2>
	<?php if ($addons) : ?>
		<ul class="subsubsub">
			<?php
			$i = 0;
			foreach ($categories as $link => $name) { $i++;
				?>
				<li><a class="<?php if ($view == $link)	echo 'current'; ?>" href="<?php echo admin_url('admin.php?page=jigoshop_extensions&view='.esc_attr($link)); ?>"><?php echo $name; ?></a><?php if ($i != sizeof($categories)) echo ' |'; ?></li><?php
			}
			?>
		</ul>
		<br class="clear" />
		<ul class="products">
			<?php
			$addons = $addons->products->$view;
			foreach ($addons as $addon) {
				echo '<li class="product">';
				echo '<a href="'.$addon->link.'" target="_blank">';
				echo '<span class="price">'.$addon->price.'</span>';
				if (!empty($addon->image)) {
					echo '<h3><img src="'.$addon->image.'"/>'.$addon->title.'</h3>';
				} else {
					echo '<h3>'.$addon->title.'</h3>';
				}
				echo '<p>'.$addon->excerpt.'</p>';
				echo '</a>';
				echo '</li>';
			}
			?>
		</ul>
	<?php else : ?>
		<p><?php printf(__('Our catalog of Jigoshop Extensions can be found on Jigoshop.com here: <a href="%s">Jigoshop Extensions Catalog</a>', 'jigoshop'), 'https://www.jigoshop.com/product-category/extensions/'); ?></p>
	<?php endif; ?>
</div>
