<?php
/**
 * @var $url string PayPal URL
 * @var $fields array Fields to send.
 */
?>
<form action="<?php echo $url; ?>" method="post" id="paypal_payment_form">
	<?php foreach($fields as $name => $value): ?>
		<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
	<?php endforeach; ?>
	<input type="submit" class="button-alt" value="<?php _e('Pay via PayPal', 'jigoshop'); ?>" />
</form>
<script type="text/javascript">
	jQuery(function($){
		$("#paypal_payment_form").payment({
			message: "<?php _e('Thank you for your order. We are now redirecting you to PayPal to make payment.', 'jigoshop'); ?>",
			redirect: "<?php _e('Redirecting...', 'jigoshop'); ?>"
		});
	});
</script>