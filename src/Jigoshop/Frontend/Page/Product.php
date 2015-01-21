<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Exception;
use Jigoshop\Frontend\NotEnoughStockException;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Product implements PageInterface
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

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, CartServiceInterface $cartService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->cartService = $cartService;
		$this->messages = $messages;

		Styles::add('jigoshop.shop.product', JIGOSHOP_URL.'/assets/css/shop/product.css', array(
			'jigoshop.shop',
			'jigoshop.vendors'
		));
		Scripts::add('jigoshop.shop.product', JIGOSHOP_URL.'/assets/js/shop/product.js', array(
			'jquery',
			'jigoshop.shop',
			'jigoshop.vendors',
		));
		$this->wp->addAction('jigoshop\template\product\before_summary', array($this, 'productImages'), 10, 1);
		$this->wp->addAction('jigoshop\template\product\after_summary', array($this, 'productTabs'), 10, 1);
		$this->wp->addAction('jigoshop\template\product\tab_panels', array($this, 'productAttributes'), 10, 2);
		$this->wp->addAction('jigoshop\template\product\tab_panels', array($this, 'productDescription'), 10, 2);
		$this->wp->doAction('jigoshop\product\assets', $wp);
	}

	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'add-to-cart') {
			$post = $this->wp->getGlobalPost();
			/** @var \Jigoshop\Entity\Product $product */
			$product = $this->productService->findForPost($post);

			try {
				$item = $this->wp->applyFilters('jigoshop\cart\add', null, $product);

				if ($item === null) {
					throw new Exception(__('Unable to add product to the cart.', 'jigoshop'));
				}

				$cart = $this->cartService->get($this->cartService->getCartIdForCurrentUser());
				$cart->addItem($item);
				$this->cartService->save($cart);

				$url = false;
				$button = '';
				switch ($this->options->get('shopping.redirect_add_to_cart')) {
					case 'cart':
						$url = $this->wp->getPermalink($this->options->getPageId(Pages::CART));
						break;
					case 'checkout':
						$url = $this->wp->getPermalink($this->options->getPageId(Pages::CHECKOUT));
						break;
					/** @noinspection PhpMissingBreakStatementInspection */
					case 'product_list':
						$url = $this->wp->getPermalink($this->options->getPageId(Pages::SHOP));
					case 'product':
					case 'same_page':
					default:
						$button = sprintf('<a href="%s" class="btn btn-warning pull-right">%s</a>', $this->wp->getPermalink($this->options->getPageId(Pages::CART)), __('View cart', 'jigoshop'));
				}

				$this->messages->addNotice(sprintf(__('%s successfully added to your cart. %s', 'jigoshop'), $product->getName(), $button));
				if ($url !== false) {
					$this->messages->preserveMessages();
					$this->wp->wpRedirect($url);
				}
			} catch(NotEnoughStockException $e) {
				if ($e->getStock() == 0) {
					$message = sprintf(__('Sorry, we do not have "%s" in stock.', 'jigoshop'), $product->getName());
				} else if ($this->options->get('products.show_stock')) {
					$message = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. We only have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $product->getName(), $e->getStock());
				} else {
					$message = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $product->getName());
				}

				$this->messages->addError($message);
			} catch(Exception $e) {
				$this->messages->addError(sprintf(__('A problem ocurred when adding to cart: %s', 'jigoshop'), $e->getMessage()));
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
		$featured = ProductHelper::getFeaturedImage($product, Options::IMAGE_LARGE);
		$featuredUrl = ProductHelper::hasFeaturedImage($product) ? $this->wp->wpGetAttachmentUrl($this->wp->getPostThumbnailId($product->getId())) : '';
		$thumbnails = $this->productService->getThumbnails($product, Options::IMAGE_THUMBNAIL);

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
		if ($product->getVisibleAttributes()) {
			$tabs['attributes'] = __('Additional information', 'jigoshop');
		}
		if ($product->getDescription()) {
			$tabs['description'] = __('Description', 'jigoshop');
		}

		$tabs = $this->wp->applyFilters('jigoshop\product\tabs', $tabs);
		$availableTabs = array_keys($tabs);

		Render::output('shop/product/tabs', array(
			'product' => $product,
			'tabs' => $tabs,
			'currentTab' => reset($availableTabs),
		));
	}

	/**
	 * @param $currentTab string Current tab name.
	 * @param $product \Jigoshop\Entity\Product Shown product.
	 */
	public function productAttributes($currentTab, $product)
	{
		Render::output('shop/product/attributes', array(
			'product' => $product,
			'currentTab' => $currentTab,
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
