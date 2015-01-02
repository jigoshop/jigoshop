<?php

namespace Jigoshop\Frontend\Page\Checkout;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class ThankYou implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var OrderServiceInterface */
	private $orderService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, OrderServiceInterface $orderService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->orderService = $orderService;

		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.user.account', JIGOSHOP_URL.'/assets/css/user/account.css');
		$styles->add('jigoshop.user.account.orders', JIGOSHOP_URL.'/assets/css/user/account/orders.css');
		$styles->add('jigoshop.user.account.orders.single', JIGOSHOP_URL.'/assets/css/user/account/orders/single.css');
		$wp->doAction('jigoshop\checkout\thank_you\assets', $wp, $styles, $scripts);
		$wp->addAction('wp_head', array($this, 'googleAnalyticsTracking'), 9999);
	}

	/**
	 * Displays Google Analytics eCommerce tracking code to add order data.
	 */
	function googleAnalyticsTracking()
	{
		// Do not track admin pages
		if ($this->wp->isAdmin()) {
			return;
		}

		// Do not track shop owners
		if ($this->wp->currentUserCan('manage_jigoshop')) {
			return;
		}

		$trackingId = $this->options->get('advanced.integration.google_analytics');

		if (empty($trackingId)) {
			return;
		}

		$order = $this->orderService->find((int)$_REQUEST['order']);
		if ($order->getKey() != $_REQUEST['key']) {
			return;
		}
		?>
		<script type="text/javascript">
			jigoshopGoogleAnalytics('require', 'ecommerce');
			jigoshopGoogleAnalytics('ecommerce:addTransaction', {
				'id': '<?php echo $order->getNumber(); ?>', // Transaction ID. Required.
				'affiliation': '<?php bloginfo('name'); ?>', // Affiliation or store name.
				'revenue': '<?php echo $order->getTotal(); ?>', // Grand Total.
				'shipping': '<?php echo $order->getShippingPrice(); ?>', // Shipping.
				'tax': '<?php echo $order->getTotalTax(); ?>' // Tax.
			});

			<?php foreach($order->getItems() as $item): /** @var $item Order\Item */ ?>
			<?php
				$product = $item->getProduct();
				if ($product instanceof Product\Variable) {
					$variation = $product->getVariation($item->getMeta('variation_id')->getValue());
				}
			?>
			jigoshopGoogleAnalytics('ecommerce:addItem', {
				'id': '<?php echo $order->getNumber(); ?>', // Transaction ID. Required.
				'name': '<?php echo $item->getName(); ?>', // Product name. Required.
				'sku': '<?php echo $product->getSku(); ?>', // SKU/code.
				'category': '<?php if (isset($variation)) echo $variation->getTitle(); ?>', // Category or variation.
				'price': '<?php echo $item->getPrice(); ?>', // Unit price.
				'quantity': '<?php echo $item->getQuantity(); ?>' // Quantity.
			});
			<?php endforeach; ?>

			jigoshopGoogleAnalytics('ecommerce:send');
		</script>
	<?php
	}

	public function action()
	{
		if (!isset($_REQUEST['order']) || !isset($_REQUEST['key'])) {
			$this->messages->addNotice(__('No order to display.', 'jigoshop'));
			$this->wp->redirectTo($this->options->getPageId(Pages::SHOP));
		}
	}

	public function render()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::THANK_YOU));
		$order = $this->orderService->find((int)$_REQUEST['order']);
		if ($order->getKey() != $_REQUEST['key']) {
			$this->messages->addError(__('Invalid security key. The order was processed.', 'jigoshop'));
			$this->wp->redirectTo($this->options->getPageId(Pages::SHOP));
		}

		return Render::get('shop/checkout/thanks', array(
			'content' => $content,
			'messages' => $this->messages,
			'order' => $order,
			'showWithTax' => $this->options->get('tax.price_tax') == 'with_tax',
			'shopUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::SHOP)),
			'getTaxLabel' => function($taxClass) use ($order) {
				return Tax::getLabel($taxClass, $order->getCustomer());
			},
		));
	}
}
