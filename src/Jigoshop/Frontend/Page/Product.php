<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Exception;
use Jigoshop\Frontend\Page;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Product implements Page
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

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages, Styles $styles,
		Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->cartService = $cartService;
		$this->messages = $messages;

		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.shop.product', JIGOSHOP_URL.'/assets/css/shop/product.css');
		$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
		$scripts->add('jigoshop.shop.product', JIGOSHOP_URL.'/assets/js/shop/product.js');
		$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
		$this->wp->addAction('jigoshop\product\before_summary', array($this, 'productImages'), 10, 1);
		$this->wp->addAction('jigoshop\product\after_summary', array($this, 'productTabs'), 10, 1);
		$this->wp->addAction('jigoshop\product\tab_panels', array($this, 'productDescription'), 10, 2);
	}


	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'add-to-cart') {
			$post = $this->wp->getGlobalPost();
			$product = $this->productService->findForPost($post);
			$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());

			try {
				$cart->addItem($product, (int)$_POST['quantity']);
				$this->cartService->save($cart);
				$button = sprintf('<a href="%s" class="btn btn-warning pull-right">%s</a>', $this->wp->getPermalink($this->options->getPageId(Pages::CART)), __('View cart', 'jigoshop'));
				$this->messages->addNotice(sprintf(__('%s successfully added to your cart. %s', 'jigoshop'), $product->getName(), $button), false);
			} catch(Exception $e) {
				// TODO: Could be improved with `NotEnoughStockException` and others
				$this->messages->addError(sprintf(__('A problem ocurred when adding to cart: %s', 'jigoshop'), $e->getMessage()), false);
			}
		}
	}

	public function render()
	{
		$post = $this->wp->getGlobalPost();
		$product = $this->productService->findForPost($post);
		return Render::get('shop/product', array(
			'product' => $product,
			'messages' => $this->messages,
		));
	}

	/**
	 * Renders images section of product page.
	 *
	 * @param $product \Jigoshop\Entity\Product The product to render data for.
	 */
	public function productImages($product)
	{
		$imageClasses = apply_filters('jigoshop\product\image_classes', array(), $product);
		$featured = ProductHelper::getFeaturedImage($product, 'shop_large');
		$featuredUrl = ProductHelper::hasFeaturedImage($product) ? $this->wp->wpGetAttachmentUrl($this->wp->getPostThumbnailId($product->getId())) : '';
		$thumbnails = $this->productService->getThumbnails($product);

		Render::output('shop/product/images', array(
			'product' => $product,
			'featured' => $featured,
			'featuredUrl' => $featuredUrl,
			'thumbnails' => $thumbnails,
			'imageClasses' => $imageClasses,
		));
	}

	/**
	 * @param $product \Jigoshop\Entity\Product Shown product.
	 */
	public function productTabs($product)
	{
		$tabs = array();
		if ($product->getDescription()) {
			$tabs['description'] = __('Description', 'jigoshop');
		}
		$tabs = $this->wp->applyFilters('jigoshop\product\tabs', $tabs);

		Render::output('shop/product/tabs', array(
			'product' => $product,
			'tabs' => $tabs,
			'currentTab' => 'description',
		));
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product \Jigoshop\Entity\Product Shown product.
	 */
	public function productDescription($currentTab, $product)
	{
		Render::output('shop/product/description', array(
			'product' => $product,
			'currentTab' => $currentTab,
		));
	}
}
