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

class ProductList implements Page
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var CartServiceInterface */
	private $cartService;
	/** @var Messages  */
	private $messages;

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages, Styles $styles)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->cartService = $cartService;
		$this->messages = $messages;

		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.shop.list', JIGOSHOP_URL.'/assets/css/shop/list.css');
	}


	public function action()
	{
		if (isset($_GET['page_id']) && $_GET['page_id'] == $this->options->getPageId(Pages::SHOP)) {
			$this->wp->wpSafeRedirect($this->wp->getPostTypeArchiveLink(Types::PRODUCT));
		}

		if (isset($_POST['action']) && $_POST['action'] == 'add-to-cart') {
			$product = $this->productService->find($_POST['item']);
			$cart = $this->cartService->get(''); // TODO: Fetch proper cart ID

			try {
				$cart->addItem($product, 1);
				$this->cartService->save($cart);
				$this->messages->addNotice(sprintf(__('%s successfully added to your cart.', 'jigoshop'), $product->getName()), false);
			} catch(Exception $e) {
				// TODO: Could be improved with `NotEnoughStockException` and others
				$this->messages->addError(sprintf(__('A problem ocurred when adding to cart: %s', 'jigoshop'), $e->getMessage()), false);
			}
		}
	}

	public function render()
	{
		$content = $this->wp->getPostField('post_content', $this->options->getPageId(Pages::SHOP));
		$query = $this->wp->getWpQuery();
		$products = $this->productService->findByQuery($query);
		return Render::get('shop', array(
			'content' => $content,
			'products' => $products,
			'product_count' => $query->max_num_pages,
			'messages' => $this->messages,
		));
	}
}
