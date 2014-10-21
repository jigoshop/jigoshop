<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Product
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var Types\Product */
	private $type;

	public function __construct(Wordpress $wp, Options $options, Types\Product $type, ProductServiceInterface $productService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->type = $type;

		$wp->addAction('wp_ajax_jigoshop.admin.product.find', array($this, 'findProduct'), 10, 0);

		$that = $this;
		$wp->addAction('add_meta_boxes_'.Types::PRODUCT, function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-product-data', __('Product Data', 'jigoshop'), array($that, 'box'), Types::PRODUCT, 'normal', 'high');
			$wp->removeMetaBox('commentstatusdiv', null, 'normal');
		});
		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts){
			if ($wp->getPostType() == Types::PRODUCT) {
				// TODO: Change settings.css into something strictly product-related
				$styles->add('jigoshop.admin.product', JIGOSHOP_URL.'/assets/css/admin/settings.css');
				$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
				$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
			}
		});
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since 		1.0
	 */
	public function box()
	{
		$post = $this->wp->getGlobalPost();
		$product = $this->productService->findForPost($post);
		$types = array();

		foreach ($this->type->getEnabledTypes() as $type) {
			$types[$type] = $this->type->getTypeName($type);
		}

		$menu = $this->wp->applyFilters('jigoshop\admin\product\menu', array(
			'general' => array('label' => __('General', 'jigoshop'), 'visible' => true),
			'advanced' => array('label' => __('Advanced', 'jigoshop'), 'visible' => true),
			'stock' => array('label' => __('Stock', 'jigoshop'), 'visible' => true),
			'sales' => array('label' => __('Sales', 'jigoshop'), 'visible' => array('simple')),
//			'inventory' => __('Inventory', 'jigoshop'),
//			'attributes' => __('Attributes', 'jigoshop'),
		));
		$taxClasses = array();
		foreach ($this->options->get('tax.classes') as $class) {
			$taxClasses[$class['class']] = $class['label'];
		}

		$tabs = $this->wp->applyFilters('jigoshop\admin\product\tabs', array(
			'general' => array(
				'product' => $product,
			),
			'stock' => array(
				'product' => $product,
			),
			'sales' => array(
				'product' => $product,
			),
			'advanced' => array(
				'product' => $product,
				'taxClasses' => $taxClasses,
			),
//			'inventory' => array(),
//			'attributes' => array(),
		));

//		add_action('admin_footer', 'jigoshop_meta_scripts');
//		wp_nonce_field('jigoshop_save_data', 'jigoshop_meta_nonce');

		Render::output('admin/product/box', array(
			'product' => $product,
			'types' => $types,
			'menu' => $menu,
			'tabs' => $tabs,
			'current_tab' => 'general',
		));
	}

	public function findProduct()
	{
		$products = $this->productService->findLike($_POST['product']);

		$result = array(
			'success' => true,
			'results' => array_map(function($item){
				/** @var $item Product */
				return array(
					'id' => $item->getId(),
					'text' => $item->getName(),
				);
			}, $products),
		);

		echo json_encode($result);
		exit;
	}
}
