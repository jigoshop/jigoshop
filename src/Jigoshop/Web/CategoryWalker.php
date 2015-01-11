<?php

namespace Jigoshop\Web;

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

	/** @var Wordpress */
	private $wp;
	/** @var string */
	private $template;

	public function __construct(Wordpress $wp, $template)
	{
		$this->wp = $wp;
		$this->template = $template;
	}

	public function start_el(&$output, $category, $depth = 0, $args = array(), $current_object_id = 0)
	{
		$name = $this->wp->applyFilters('list_product_cats', $category->name, $category);

		if (!isset($args['value'])) {
			$args['value'] = ($category->taxonomy == Types::PRODUCT_CATEGORY ? 'slug' : 'id');
		}

		$value = $args['value'] == 'slug' ? $category->slug : $category->term_id;

		$output .= Render::get($this->template, array(
			'depth' => $depth,
			'term' => $category,
			'value' => $value,
			'name' => $name,
			'selected' => $args['selected'],
			'show_count' => $args['show_count'],
			'count' => $category->count,
		));
	}
}
