<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Content to display.
 * @var $order \Jigoshop\Entity\Order The order.
 */
?>

<h1><?php printf(__('Checkout &rang; Payment &rang; %s', 'jigoshop'), $order->getTitle()); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<div class="payment">
	<?php echo $content; ?>
</div>
