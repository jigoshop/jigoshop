<?php

namespace Jigoshop\Admin\Page\Products;

use Jigoshop\Core\Types;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

/**
 * Create HTML dropdown list of Product Categories.
 */
class CategoryWalker extends \Walker_CategoryDropdown
{
	public $tree_type = 'category';
	public $db_fields = array('parent' => 'parent', 'id' => 'term_id', 'slug' => 'slug');

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	public function start_el(&$output, $category, $depth = 0, $args = array(), $current_object_id = 0)
	{
		$name = $this->wp->applyFilters('list_product_cats', $category->name, $category);

		if (!isset($args['value'])) {
			$args['value'] = ($category->taxonomy == Types::PRODUCT_CATEGORY ? 'slug' : 'id');
		}

		$value = $args['value'] == 'slug' ? $category->slug : $category->term_id;

		$output .= Render::get('admin/products/categoryFilter/item', array(
			'depth' => $depth,
			'value' => $value,
			'name' => $name,
			'selected' => $args['selected'],
			'show_count' => $args['show_count'],
			'count' => $category->count,
		));
	}
}
