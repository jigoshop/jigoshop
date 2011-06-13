<?php

global $columns, $post;

jigoshop::show_messages();

$loop = 0;
if (!isset($columns) || !$columns) $columns = 4;
$limit = get_option('posts_per_page');
$found = false;
ob_start();
if (have_posts()) : while (have_posts()) : the_post(); 
					
	$_product = &new jigoshop_product( $post->ID );
	
	$loop++;
	
	$found = true;
	
	?><li class="product <?php if ($loop%$columns==0) echo 'last'; if (($loop-1)%$columns==0) echo 'first'; ?>"><a href="<?php the_permalink(); ?>">
		<?php 
			if ($_product->is_on_sale()) echo '<span class="onsale">'.__('Sale!', 'jigoshop').'</span>';
		
			if (has_post_thumbnail()) the_post_thumbnail('shop_small'); 
			else echo '<img src="'.jigoshop::plugin_url(). '/assets/images/placeholder.png" alt="Placeholder" width="'.jigoshop::get_var('shop_small_w').'" height="'.jigoshop::get_var('shop_small_h').'" />'; 
		?>
		<strong><?php the_title(); ?></strong>
		<span class="price"><?php echo $_product->get_price_html(); ?></span>
	</a>
	<a href="<?php echo $_product->add_to_cart_url(); ?>" class="button"><?php _e('Add to cart', 'jigoshop'); ?></a>
	</li><?php 
	
	if ($loop==$limit) break;
	
endwhile; else :
	
	$found = false;
	
endif;

if (!$found) :
	echo '<p class="info">'.__('No products found which match your selection.', 'jigoshop').'</p>'; 
else :
	
	$found_posts = ob_get_clean();
	echo '<ul class="products">' . $found_posts . '</ul><div class="clear"></div>';
	
	
endif;