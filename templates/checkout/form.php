<?php if (!is_user_logged_in()) jigoshop_get_template('checkout/login.php'); ?>

<form name="checkout" method="post" class="checkout" action="<?php echo jigoshop_cart::get_checkout_url(); ?>">
	
	<div class="col2-set" id="customer_details">
		<div class="col-1">

			<?php do_action('jigoshop_checkout_billing'); ?>
						
		</div>
		<div class="col-2">
		
			<?php do_action('jigoshop_checkout_shipping'); ?>
					
		</div>
	</div>
	
	<h3 id="order_review_heading"><?php _e('Your order', 'jigoshop'); ?></h3>
	
	<?php jigoshop_get_template('checkout/review_order.php'); ?>
	
</form>