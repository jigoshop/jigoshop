<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Jigoshop_Report_Stock extends WP_List_Table
{
	protected $max_items;

	public function __construct()
	{
		parent::__construct(array(
			'singular' => __('Stock', 'jigoshop'),
			'plural' => __('Stock', 'jigoshop'),
			'ajax' => false
		));
	}

	public function no_items()
	{
		_e('No products found.', 'jigoshop');
	}

	public function display_tablenav($position)
	{
		if ($position != 'top') {
			parent::display_tablenav($position);
		}
	}

	public function output()
	{
		$this->prepare_items();
		echo '<div id="poststuff" class="jigoshop-reports-wide">';
		$this->display();
		echo '</div>';
	}

	public function prepare_items()
	{
		$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
		$current_page = absint($this->get_pagenum());
		$per_page = apply_filters('jigoshop_admin_stock_report_products_per_page', 1);
		//$this->max_items
		$this->get_items($current_page, $per_page);

		$this->set_pagination_args(array(
			'total_items' => $this->max_items,
			'per_page' => $per_page,
			'total_pages' => ceil($this->max_items / $per_page)
		));
	}

	public function get_columns()
	{

		$columns = array(
			'product' => __('Product', 'jigoshop'),
			'parent' => __('Parent', 'jigoshop'),
			'stock_level' => __('Units in stock', 'jigoshop'),
			'stock_status' => __('Stock status', 'jigoshop'),
			'actions' => __('Actions', 'jigoshop'),
		);

		return $columns;
	}

	public function column_default($item, $column_name)
	{
		global $product;

		if (!$product || $product->id !== $item->id) {
			$product = new jigoshop_product($item->id);
		}

		switch ($column_name) {
			case 'product' :
				if ($sku = $product->get_sku()) {
					echo $sku.' - ';
				}

				echo $product->get_title();

				// Get variation data
				if ($product->is_type('variation')) {
					$list_attributes = array();
					$attributes = $product->get_available_attributes_variations();

					foreach ($attributes as $name => $attribute) {
						$list_attributes[] = $product->attribute_label(str_replace('pa_', '', $name)).': <strong>'.$attribute.'</strong>';
					}

					echo '<div class="description">'.implode(', ', $list_attributes).'</div>';
				}
				break;
			case 'parent' :
				if ($item->parent) {
					echo get_the_title($item->parent);
				} else {
					echo '-';
				}
				break;
			case 'stock_status' :
				if ($product->is_in_stock() || (!isset($product->meta['stock_manage']) && !isset($product->meta['stock_status']) && $product->get_stock() > 0)) {
					echo '<mark class="instock">'.__('In stock', 'jigoshop').'</mark>';
				} else {
					echo '<mark class="outofstock">'.__('Out of stock', 'jigoshop').'</mark>';
				}
				break;
			case 'stock_level' :
				echo $product->get_stock();
				break;
			case 'actions' :
				?><p>
				<?php
				$actions = array();
				$action_id = $item->parent != 0 ? $item->parent : $item->id;

				$actions['edit'] = array(
					'url' => admin_url('post.php?post='.$action_id.'&action=edit'),
					'name' => __('Edit', 'jigoshop'),
					'action' => "edit"
				);

				if ($product->is_visible()) {
					$actions['view'] = array(
						'url' => get_permalink($action_id),
						'name' => __('View', 'jigoshop'),
						'action' => "view"
					);
				}

				$actions = apply_filters('jigoshop_admin_stock_report_product_actions', $actions, $product);

				foreach ($actions as $action) {
					printf('<a class="button tips %s" href="%s" data-tip="%s '.__('product', 'jigoshop').'">%s</a>', $action['action'], esc_url($action['url']), esc_attr($action['name']), esc_attr($action['name']));
				}
				?>
				</p><?php
				break;
		}
	}
}
