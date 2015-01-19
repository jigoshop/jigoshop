<?php
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product Product object.
 * @var $featured string Featured image.
 * @var $featuredUrl string URL to featured image.
 * @var $thumbnails array List of product thumbnails.
 * @var $imageClasses array List of classes to attach to image.
 */
?>
<div class="images">
	<?php if (Product::isOnSale($product)): ?>
		<span class="on-sale"><?php _e('Sale!', 'jigoshop'); ?></span>
	<?php endif; ?>
	<?php do_action('jigoshop\template\product\before_featured_image', $product); ?>
	<a href="<?php echo $featuredUrl; ?>" class="<?php echo join(' ', $imageClasses); ?>" data-lightbox="product-gallery"><?php echo $featured; ?></a>
	<?php do_action('jigoshop\template\product\before_thumbnails', $product); ?>
	<div class="thumbnails">
		<?php foreach($thumbnails as $id => $thumbnail): ?>
			<a href="<?php echo $thumbnail['url']; ?>" data-lightbox="product-gallery" data-title="<?php echo $thumbnail['title']; ?>" title="<?php echo $thumbnail['title']; ?>" class="zoom"><?php echo $thumbnail['image']; ?></a>
		<?php endforeach; ?>
	</div>
	<?php do_action('jigoshop\template\product\after_thumbnails', $product); ?>
</div>
