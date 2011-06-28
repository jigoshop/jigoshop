<?php 
	global $post, $_product;
	
	if (isset($_COOKIE["current_tab"])) $current_tab = $_COOKIE["current_tab"]; else $current_tab = '#tab-description'; 
?>
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