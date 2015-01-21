<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\ProductCategory;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

class ProductCategories
{
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;

		$wp->addAction(sprintf('%s_add_form_fields', Types::PRODUCT_CATEGORY), array(
			$this,
			'showThumbnail'
		));
		$wp->addAction(sprintf('%s_edit_form_fields', Types::PRODUCT_CATEGORY), array(
			$this,
			'showThumbnail'
		));
		$wp->addAction('created_term', array($this, 'saveThumbnail'), 10, 3);
		$wp->addAction('edit_term', array($this, 'saveThumbnail'), 10, 3);
		$wp->addAction(sprintf('delete_%s', Types::PRODUCT_CATEGORY), array($this, 'delete'));

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			$wp->wpEnqueueMedia();
			Scripts::add('jigoshop.admin.product_categories', JIGOSHOP_URL.'/assets/js/admin/product_categories.js', array(
				'jquery',
				'jigoshop.media'
			));
			Scripts::localize('jigoshop.admin.product_categories', 'jigoshop_admin_product_categories', array(
				'category_name' => Types::PRODUCT_CATEGORY,
				'placeholder' => JIGOSHOP_URL.'/assets/images/placeholder.png',
			));

			$wp->doAction('jigoshop\admin\product_categories\assets', $wp);
		});
	}

	public function showThumbnail($term)
	{
		$termId = 0;
		if (is_object($term)) {
			$termId = $term->term_id;
		}

		$image = ProductCategory::getImage($termId);
		Render::output('admin/product_categories/thumbnail', array(
			'image' => $image,
		));
	}

	public function saveThumbnail($termId, $ttId, $taxonomy)
	{
		if ($taxonomy != Types::PRODUCT_CATEGORY) {
			return;
		}

		$thumbnail = isset($_POST[Types::PRODUCT_CATEGORY.'_thumbnail_id']) ? $_POST[Types::PRODUCT_CATEGORY.'_thumbnail_id'] : false;
		if (!is_numeric($thumbnail)) {
			return;
		}

		update_metadata(Core::TERMS, $termId, 'thumbnail_id', (int)$thumbnail);
	}

	public function delete($termId)
	{
		$termId = (int)$termId;

		if (!$termId) {
			return;
		}

		$wpdb = $this->wp->getWPDB();
		/** @noinspection PhpUndefinedFieldInspection */
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->jigoshop_termmeta} WHERE `jigoshop_term_id` = %d", $termId));
	}
}
