<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Formatter;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Products
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var Types\Product */
	private $type;

	public function __construct(Wordpress $wp, Options $options, Types\Product $type, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->type = $type;

		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::PRODUCT), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::PRODUCT), array($this, 'displayColumn'), 2);
		$wp->addAction('restrict_manage_posts', array($this, 'categoryFilter'));
		$wp->addAction('restrict_manage_posts', array($this, 'typeFilter'));
		$wp->addAction('pre_get_posts', array($this, 'setTypeFilter'));
		$wp->addAction('wp_ajax_jigoshop.admin.products.feature_product', array($this, 'ajaxFeatureProduct'));

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			if ($wp->getPostType() == Types::PRODUCT) {
				Scripts::add('jigoshop.admin.products', JIGOSHOP_URL.'/assets/js/admin/products.js', array(
					'jquery',
					'jigoshop.helpers'
				));
				Scripts::localize('jigoshop.admin.products', 'jigoshop_admin_products', array(
					'ajax' => $wp->getAjaxUrl(),
				));

				$wp->doAction('jigoshop\admin\products\assets', $wp);
			}
		});
	}

	public function ajaxFeatureProduct()
	{
		/** @var Product $product */
		$product = $this->productService->find((int)$_POST['product_id']);
		$product->setFeatured(!$product->isFeatured());
		$this->productService->save($product);

		echo json_encode(array(
			'success' => true,
		));
		exit;
	}

	public function columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'thumbnail' => null,
			'title' => _x('Name', 'product', 'jigoshop'),
			'sku' => _x('SKU', 'product', 'jigoshop'),
			'featured' => sprintf(
				'<span class="glyphicon glyphicon-star" aria-hidden="true"></span> <span class="sr-only">%s</span>',
				_x('Is featured?', 'product', 'jigoshop')
			),
			'type' => _x('Type', 'product', 'jigoshop'),
			'stock' => _x('Stock', 'product', 'jigoshop'),
			'price' => _x('Price', 'product', 'jigoshop'),
			'creation' => _x('Created at', 'product', 'jigoshop'),
		);

		if($this->options->get('enable_sku', 'yes') !== 'yes'){
			unset($columns['sku']);
		}
		if($this->options->get('manage_stock', 'yes') !== 'yes'){
			unset($columns['stock']);
		}

		return $columns;
	}

	public function displayColumn($column)
	{
		$post = $this->wp->getGlobalPost();
		if($post === null){
			return;
		}

		/** @var Product $product */
		$product = $this->productService->find($post->ID);
		switch ($column) {
			case 'thumbnail':
				echo ProductHelper::getFeaturedImage($product, Options::IMAGE_THUMBNAIL);
				break;
			case 'price':
				echo ProductHelper::getPriceHtml($product);
				break;
			case 'featured':
				echo ProductHelper::isFeatured($product);
				break;
			case 'stock':
				echo ProductHelper::getStock($product);
				break;
			case 'type':
				echo $this->type->getType($product->getType())->getName();
				break;
			case 'sku':
				echo $product->getSku();
				break;
			case 'creation':
				$timestamp = strtotime($post->post_date);
				echo Formatter::date($timestamp);

				if($product->isVisible()){
					echo '<br /><strong>'.__('Visible in', 'jigoshop').'</strong>: ';
					switch($product->getVisibility()){
						case ProductEntity::VISIBILITY_SEARCH:
							echo __('Search only', 'jigoshop');
							break;
						case ProductEntity::VISIBILITY_CATALOG:
							echo __('Catalog only', 'jigoshop');
							break;
						case ProductEntity::VISIBILITY_PUBLIC:
							echo __('Catalog and search', 'jigoshop');
							break;
					}
				}
				break;
		}
	}
	/**
	 * Filter products by category, uses slugs for option values.
	 * Props to: Andrew Benbow - chromeorange.co.uk
	 */
	public function categoryFilter()
	{
		$type = $this->wp->getTypeNow();
		if ($type != Types::PRODUCT) {
			return;
		}

		$query = array(
			'pad_counts' => 1,
			'hierarchical' => true,
			'hide_empty' => true,
			'show_count' => true,
			'selected' => $this->wp->getQueryParameter(Types::PRODUCT_CATEGORY),
		);

		$terms = $this->wp->getTerms(Types::PRODUCT_CATEGORY, $query);
		if (!$terms) {
			return;
		}

		$current = isset($_GET[Types::PRODUCT_CATEGORY]) ? $_GET[Types::PRODUCT_CATEGORY] : '';
		$walker = new \Jigoshop\Web\CategoryWalker($this->wp, 'admin/products/categoryFilter/item');

		Render::output('admin/products/categoryFilter', array(
			'terms' => $terms,
			'current' => $current,
			'walker' => $walker,
			'query' => $query,
		));
	}

	/**
	 * Filter products by type
	 */
	public function typeFilter()
	{
		$type = $this->wp->getTypeNow();
		if ($type != Types::PRODUCT) {
			return;
		}

		// Get all active terms
		$types = array();
		foreach ($this->type->getEnabledTypes() as $type) {
			/** @var $type Types\Product\Type */
			$types[$type->getId()] = array(
				'label' => $type->getName(),
				'count' => $this->getTypeCount($type),
			);
		}
		$currentType = isset($_GET['product_type']) ? $_GET['product_type'] : '';

		Render::output('admin/products/typeFilter', array(
			'types' => $types,
			'current' => $currentType
		));
	}

	/**
	 * Finds and returns number of products of specified type.
	 *
	 * @param $type Types\Product\Type Type class.
	 * @return int Count of the products.
	 */
	private function getTypeCount($type)
	{
		$wpdb = $this->wp->getWPDB();
		return $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(*) FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE pm.meta_key = %s AND pm.meta_value = %s
		", array('type', $type->getId())));
	}

	/**
	 * @param $query \WP_Query
	 */
	public function setTypeFilter($query)
	{
		if (isset($_GET['product_type']) && in_array($_GET['product_type'], array_keys($this->type->getEnabledTypes()))) {
			$meta = $query->meta_query;
			$meta[] = array(
				'key' => 'type',
				'value' => $_GET['product_type'],
			);
			$query->set('meta_query', $meta);
		}
	}
}
