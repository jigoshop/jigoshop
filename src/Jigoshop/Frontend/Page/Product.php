<?php

namespace Jigoshop\Frontend\Page;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Frontend\Page;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
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
	/** @var Messages  */
	private $messages;

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, Messages $messages, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->messages = $messages;
		$styles->add('jigoshop.shop', JIGOSHOP_URL.'/assets/css/shop.css');
		$styles->add('jigoshop.shop.product', JIGOSHOP_URL.'/assets/css/shop/product.css');
		$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
		$scripts->add('jigoshop.shop.product', JIGOSHOP_URL.'/assets/js/shop/product.js');
		$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
		$this->wp->addAction('jigoshop\product\before_summary', array($this, 'productImages'), 10, 1);
	}


	public function action()
	{
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
}
