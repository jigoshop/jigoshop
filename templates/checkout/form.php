<?php
/**
 * Checkout form template
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
?>

<?php do_action('before_checkout_form');
// filter hook for include new pages inside the payment method
$get_checkout_url = apply_filters( 'jigoshop_get_checkout_url', jigoshop_cart::get_checkout_url() ); ?>

<form name="checkout" method="post" class="checkout" action="<?php echo esc_url( $get_checkout_url ); ?>">

	<h3 id="order_review_heading"><?php _e('Your Order', 'jigoshop'); ?></h3>

	<?php do_action('jigoshop_checkout_order_review'); ?>

	<div class="col2-set" id="customer_details">
		<div class="col-1">

			<?php do_action('jigoshop_checkout_billing'); ?>

		</div>
		<div class="col-2">

			<?php do_action('jigoshop_checkout_shipping'); ?>

		</div>
	</div>

	<h3 id="payment_methods_heading"><?php _e('Payment Methods', 'jigoshop'); ?></h3>

	<?php do_action('jigoshop_checkout_payment_methods'); ?>

</form>

<?php do_action('after_checkout_form'); ?>