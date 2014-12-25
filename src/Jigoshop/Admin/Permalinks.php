<?php

namespace Jigoshop\Admin;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Frontend\Pages as FrontendPages;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class Permalinks
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;

		$wp->addAction('current_screen', array($this, 'init'));
		$this->save();
	}

	/**
	 * Init our settings
	 */
	public function init()
	{
		// Add a section to the permalinks page
		$this->wp->addSettingsSection('jigoshop-permalink', __('Product permalink base', 'jigoshop'), array($this, 'settings'), 'permalink');

		// Add our settings
		$this->wp->addSettingsField(
			'jigoshop_product_category_slug',
			__('Product category base', 'jigoshop'),
			array($this, 'product_category_slug_input'),
			'permalink',
			'optional'
		);
		$this->wp->addSettingsField(
			'jigoshop_product_tag_slug',
			__('Product tag base', 'jigoshop'),
			array($this, 'product_tag_slug_input'),
			'permalink',
			'optional'
		);
	}

	/**
	 * Show a slug input box.
	 */
	public function product_category_slug_input()
	{
		$permalink = $this->options->get('permalinks.category');
		?>
		<input name="jigoshop_product_category_slug" type="text" class="regular-text code" value="<?php echo $permalink; ?>" placeholder="<?php echo _x('product-category', 'slug', 'jigoshop') ?>" />
		<?php
	}

	/**
	 * Show a slug input box.
	 */
	public function product_tag_slug_input()
	{
		$permalink = $this->options->get('permalinks.tag');
		?>
		<input name="jigoshop_product_tag_slug" type="text" class="regular-text code" value="<?php echo $permalink; ?>" placeholder="<?php echo _x('product-tag', 'slug', 'jigoshop') ?>" />
		<?php
	}

	/**
	 * Show the settings
	 */
	public function settings()
	{
		echo '<p>'.__('These settings control the permalinks used for products. These settings only apply when <strong>not using "default" permalinks above</strong>.', 'jigoshop').'</p>';

		$helpers = $this->wp->getHelpers();
		$permalink = $helpers->trailingslashit($this->options->get('permalinks.product'));

		// Get shop page
		$shopPageId = $this->options->getPageId(FrontendPages::SHOP);
		$base = urldecode(($shopPageId > 0 && $this->wp->getPost($shopPageId)) ? $this->wp->getPageUri($shopPageId) : _x('shop', 'default-slug', 'jigoshop'));
		$productBase = _x('product', 'default-slug', 'jigoshop');

		$structures = array(
			0 => '',
			1 => '/'.$helpers->trailingslashit($productBase),
			2 => '/'.$helpers->trailingslashit($base),
			3 => '/'.$helpers->trailingslashit($base).'%'.Types::PRODUCT_CATEGORY.'%'
		);
		Render::output('admin/permalinks', array(
			'permalink' => $permalink,
			'structures' => $structures,
			'shopPageId' => $shopPageId,
			'base' => $base,
			'productBase' => $productBase
		));
	}

	/**
	 * Save the settings
	 */
	private function save()
	{
		// We need to save the options ourselves; settings api does not trigger save for the permalinks page
		if (isset($_POST['permalink_structure']) || isset($_POST['category_base']) && isset($_POST['product_permalink'])) {
			// Cat and tag bases
			$categorySlug = trim(strip_tags($_POST['jigoshop_product_category_slug']));
			$tagSlug = trim(strip_tags($_POST['jigoshop_product_tag_slug']));

			$permalinks = $this->options->get('permalinks');

			$helpers = $this->wp->getHelpers();
			$permalinks['category'] = $helpers->untrailingslashit($categorySlug);
			$permalinks['tag'] = $helpers->untrailingslashit($tagSlug);

			// Product base
			$product_permalink = trim(strip_tags($_POST['product_permalink']));

			if ($product_permalink == 'custom') {
				// Get permalink without slashes
				$product_permalink = trim(strip_tags($_POST['product_permalink_structure']), '/');

				// This is an invalid base structure and breaks pages
				if ('%'.Types::PRODUCT_CATEGORY.'%' == $product_permalink) {
					$product_permalink = _x('product', 'slug', 'jigoshop').'/'.$product_permalink;
				}
			} elseif (empty($product_permalink)) {
				$product_permalink = false;
			}

			$permalinks['product'] = $helpers->untrailingslashit($product_permalink);

			// Shop base may require verbose page rules if nesting pages
			$shopPageId = $this->options->getPageId(FrontendPages::SHOP);
			$shop_permalink = urldecode(($shopPageId > 0 && $this->wp->getPost($shopPageId)) ? $this->wp->getPageUri($shopPageId) : _x('shop', 'default-slug', 'jigoshop'));
			if ($shopPageId && trim($permalinks['product'], '/') === $shop_permalink) {
				$permalinks['verbose'] = true;
			}

			$this->options->update('permalinks', $permalinks);
			$this->wp->getRewrite()->flush_rules();
		}
	}
}
