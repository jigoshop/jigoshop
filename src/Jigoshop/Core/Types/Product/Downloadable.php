<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Api\DownloadFile;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Downloadable as Entity;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class Downloadable implements Type
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Messages */
	private $messages;

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
	}

	/**
	 * Returns identifier for the type.
	 *
	 * @return string Type identifier.
	 */
	public function getId()
	{
		return Entity::TYPE;
	}

	/**
	 * Returns human-readable name for the type.
	 *
	 * @return string Type name.
	 */
	public function getName()
	{
		return __('Downloadable', 'jigoshop');
	}

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass()
	{
		return '\Jigoshop\Entity\Product\Downloadable';
	}

	/**
	 * Initializes product type.
	 *
	 * @param Wordpress $wp WordPress Abstraction Layer
	 * @param array $enabledTypes List of all available types.
	 */
	public function initialize(Wordpress $wp, array $enabledTypes)
	{
		$wp->addFilter('jigoshop\cart\add', array($this, 'addToCart'), 10, 2);
		$wp->addFilter('jigoshop\emails\order_item', array($this, 'emailLink'), 10, 3);
//		$wp->addFilter('jigoshop\core\types\variable\subtypes', array($this, 'addVariableSubtype'), 10, 1); // TODO: Enable variable subtypes changing
		$wp->addAction('jigoshop\order\before\\'.Order\Status::PROCESSING, array($this, 'updateProcessingStatus'));
		$wp->addFilter('jigoshop\product\reduce_stock_status', array($this, 'reduceStockStatus'), 10, 2);

		$wp->addAction('jigoshop\admin\product\assets', array($this, 'addAssets'), 10, 3);
		$wp->addFilter('jigoshop\admin\product\menu', array($this, 'addProductMenu'));
		$wp->addFilter('jigoshop\admin\product\tabs', array($this, 'addProductTab'), 10, 2);
		$wp->addAction('jigoshop\admin\variation', array($this, 'addVariationFields'), 10, 2);
	}

	/**
	 * @param $status string Status name.
	 * @param $order Order Order to reduce stock for.
	 * @return string Status to reduce stock for.
	 */
	public function reduceStockStatus($status, $order)
	{
		$downloadable = true;
		foreach ($order->getItems() as $item) {
			/** @var $item Order\Item */
			$downloadable &= $item->getType() == Entity::TYPE;
		}

		if ($downloadable) {
			return Order\Status::COMPLETED;
		}

		return $status;
	}

	/**
	 * Checks whether order is consists of downloadable products only and changes PROCESSING into COMPLETED status.
	 *
	 * @param $order Order
	 */
	public function updateProcessingStatus($order)
	{
		$downloadable = true;
		foreach ($order->getItems() as $item) {
			/** @var $item Order\Item */
			$downloadable &= $item->getType() == Entity::TYPE;
		}

		if ($downloadable) {
			$order->setStatus(Order\Status::COMPLETED, __('Complete downloadable only order.', 'jigoshop'));
		}
	}

	/**
	 * Renders additional fields for variations.
	 *
	 * @param $variation Product\Variable\Variation
	 * @param $product Product\Variable
	 */
	public function addVariationFields($variation, $product)
	{
		Render::output('admin/product/box/variations/variation/downloadable', array(
			'variation' => $variation,
			'product' => $variation->getProduct(),
			'parent' => $product,
		));
	}

	/**
	 * Adds downloadable as proper subtype for variations.
	 *
	 * @param $subtypes array Current list of subtypes.
	 * @return array Updated list of subtypes.
	 */
	public function addVariableSubtype($subtypes) {
		$subtypes[] = Entity::TYPE;
		return $subtypes;
	}

	/**
	 * @param $value
	 * @param $product
	 * @return null
	 */
	public function addToCart($value, $product)
	{
		if ($product instanceof Entity) {
			$item = new Item();
			$item->setName($product->getName());
			$item->setPrice($product->getPrice());
			$item->setQuantity(1);
			$item->setProduct($product);

			$meta = new Item\Meta('file', $product->getUrl());
			$item->addMeta($meta);
			$meta = new Item\Meta('downloads', $product->getLimit());
			$item->addMeta($meta);

			return $item;
		}

		return $value;
	}

	/**
	 * @param $result string Current email message.
	 * @param $item Order\Item Item to display.
	 * @param $order Order Order being displayed.
	 * @return string
	 */
	public function emailLink($result, $item, $order)
	{
		$product = $item->getProduct();
		if ($product instanceof Product\Downloadable && in_array($order->getStatus(), array(Order\Status::COMPLETED, Order\Status::PROCESSING))) {
			$url = $this->wp->getHelpers()->addQueryArg(array('file' => $order->getKey().'.'.$order->getId().'.'.$item->getKey(), Api::getUrl(DownloadFile::NAME)));

			$result .= PHP_EOL.__('Your download link for this file is:', 'jigoshop');
			$result .= PHP_EOL.' - '.$url;
		}

		return $result;
	}

	/**
	 * @param Wordpress $wp
	 * @param Styles $styles
	 * @param Scripts $scripts
	 */
	public function addAssets(Wordpress $wp, Styles $styles, Scripts $scripts)
	{
		$scripts->add('jigoshop.admin.product.downloadable', JIGOSHOP_URL.'/assets/js/admin/product/downloadable.js', array('jquery', 'jigoshop.helpers'));
	}

	/**
	 * Updates product menu.
	 *
	 * @param $menu array
	 * @return array
	 */
	public function addProductMenu($menu)
	{
		$menu['downloads'] = array('label' => __('Downloads', 'jigoshop'), 'visible' => array(Product\Downloadable::TYPE));
		$menu['advanced']['visible'][] = Product\Downloadable::TYPE;
		$menu['stock']['visible'][] = Product\Downloadable::TYPE;
		$menu['sales']['visible'][] = Product\Downloadable::TYPE;
		return $menu;
	}

	/**
	 * Updates product tabs.
	 *
	 * @param $tabs array
	 * @param $product Product
	 * @return array
	 */
	public function addProductTab($tabs, $product)
	{
		$tabs['downloads'] = array(
			'product' => $product,
		);
		return $tabs;
	}
}
