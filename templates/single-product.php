<?php get_header('shop'); ?>

<div id="container"><div id="content" role="main">

<?php if (function_exists('jigoshop_breadcrumb')) jigoshop_breadcrumb(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); 

	global $_product;
	
	$_product = &new jigoshop_product( $post->ID ); 
	
	if (!$_product->is_visible() && $post->post_parent > 0) : wp_safe_redirect(get_permalink($post->post_parent)); exit; endif;
	
	if (!$_product->is_visible()) : wp_safe_redirect(home_url()); exit; endif;
	
	jigoshop::show_messages();
	?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
			<?php if ($_product->is_on_sale()) echo '<span class="onsale">'.__('Sale!', 'jigoshop').'</span>'; ?>
			
			<?php jigoshop_get_template( 'product/images.php' ); ?>
			
			<div class="summary">

				<h1 class="product_title entry-title"><?php the_title(); ?></h1>
				
				<p class="price"><?php echo $_product->get_price_html(); ?></p>
				
				<?php if ($post->post_excerpt) echo wpautop(wptexturize($post->post_excerpt)); ?>

				<?php
					if ( $_product->is_type('simple') ) jigoshop_get_template( 'product/simple/add-to-cart.php' );
					elseif ( $_product->is_type('downloadable') ) jigoshop_get_template( 'product/downloadable/add-to-cart.php' );
					elseif ( $_product->is_type('grouped') ) jigoshop_get_template( 'product/grouped/add-to-cart.php' );
					elseif ( $_product->is_type('virtual') ) jigoshop_get_template( 'product/virtual/add-to-cart.php' );
				?>
				
				<div class="clear"></div>

				<div class="entry-meta"><?php if ($_product->is_type('simple')) : ?><span class="sku">SKU: <?php echo $_product->sku; ?>.</span> <?php endif; ?><?php echo $_product->get_categories( ', ', 'Posted in ', '.'); ?> <?php echo $_product->get_tags( ', ', 'Tagged as ', '.'); ?></div>
				
				<?php
					if (get_option('jigoshop_sharethis')) :
						echo '<div class="social">
							<iframe src="https://www.facebook.com/plugins/like.php?href='.urlencode(get_permalink($post->ID)).'&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
							<span class="st_email"></span><span class="st_twitter"></span><span class="st_sharethis"></span>
						</div>';
					endif;
				?>
				
			</div>
			
			<div class="clear"></div>
			
			<?php if (isset($_COOKIE["current_tab"])) $current_tab = $_COOKIE["current_tab"]; else $current_tab = '#tab-description'; ?>
			<div id="tabs">
				<ul class="tabs">
					<li <?php if ($current_tab=='#tab-description') echo 'class="active"'; ?>><a href="#tab-description"><?php _e('Description', 'jigoshop'); ?></a></li>
					<?php if ($_product->has_attributes()) : ?><li <?php if ($current_tab=='#tab-attributes') echo 'class="active"'; ?>><a href="#tab-attributes"><?php _e('Additional Information', 'jigoshop'); ?></a></li><?php endif; ?>
					<?php if ( comments_open() ) : ?><li <?php if ($current_tab=='#tab-reviews') echo 'class="active"'; ?>><a href="#tab-reviews"><?php _e('Reviews', 'jigoshop'); ?><?php echo comments_number(' (0)', ' (1)', ' (%)'); ?></a></li><?php endif; ?>
				</ul>			
				<div class="panel" id="tab-description">
					<?php jigoshop_get_template( 'product/description.php' ); ?>
				</div>
				<?php if ($_product->has_attributes()) : ?><div class="panel" id="tab-attributes">
					<?php jigoshop_get_template( 'product/attributes.php' ); ?>
				</div><?php endif; ?>
				<?php if ( comments_open() ) : ?><div class="panel" id="tab-reviews">
					<?php comments_template(); ?>
				</div><?php endif; ?>
			</div>
				
			<?php do_action('after_product'); ?>
			
			<?php jigoshop_get_template( 'product/related.php' ); ?>
			
			<div class="clear"></div>

		</div>

<?php endwhile; ?>

</div></div>

<?php get_sidebar('shop'); ?>
<?php get_footer('shop'); ?>