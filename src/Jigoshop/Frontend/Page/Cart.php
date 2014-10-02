<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Frontend\Page;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Cart implements Page
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var Cart */
	private $cart;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, CartServiceInterface $cartService, ProductServiceInterface $productService, Styles $styles,
		Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->cartService = $cartService;
		$this->productService = $productService;
		$this->cart = $cartService->get(''); // TODO: Properly find user's cart ID, proposition: current user ID, if not logged - some random string

		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.shop.cart', JIGOSHOP_URL.'/assets/css/shop/cart.css');
		$scripts->add('jigoshop.shop.cart', JIGOSHOP_URL.'/assets/js/shop/cart.js');

		// TODO: Ajax update cart action
	}


	public function action()
	{
		// TODO: Update cart action

		if (isset($_GET['action']) && isset($_GET['item']) && $_GET['action'] === 'remove-item' && is_numeric($_GET['item'])) {
			$this->cart->removeItem((int)$_GET['item']);
			$this->cartService->save($this->cart);
			$this->messages->addNotice(__('Successfully removed item from cart.', 'jigoshop'), false);
		}
	}

	public function render()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::CART));

		return Render::get('shop/cart', array(
			'content' => $content,
			'cart' => $this->cart,
			'messages' => $this->messages,
			'productService' => $this->productService,
			'shopUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::SHOP)),
		));
	}
}
