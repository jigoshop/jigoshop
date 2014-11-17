<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class Variable extends Product implements Shippable, Saleable
{
	const TYPE = 'variable';

	private $variations = array();
	/** @var Attributes\Sales */
	private $sales;

	/**
	 * Initializes product type with custom actions.
	 *
	 * @param Wordpress $wp Wordpress Abstraction Layer
	 */
	public static function initialize(Wordpress $wp)
	{
		$wp->addAction('jigoshop\admin\product_attribute\add', __CLASS__.'::addProductAttribute');
		$wp->addAction('jigoshop\admin\product\assets', __CLASS__.'::addProductAssets', 10, 3);
	}

	/**
	 * @param Attribute $attribute
	 */
	public static function addProductAttribute($attribute)
	{
		if ($attribute instanceof Attribute\Variable) {
			if (isset($_POST['options']) && isset($_POST['options']['is_variable'])) {
				$attribute->setVariable($_POST['options']['is_variable'] === 'true');
			}
		}
	}

	/**
	 * @param Wordpress $wp
	 * @param Styles $styles
	 * @param Scripts $scripts
	 */
	public static function addProductAssets(Wordpress $wp, Styles $styles, Scripts $scripts)
	{
		$styles->add('jigoshop.admin.product.variable', JIGOSHOP_URL.'/assets/css/admin/product/variable.css');
		$scripts->add('jigoshop.admin.product.variable', JIGOSHOP_URL.'/assets/js/admin/product/variable.js', array('jquery'));
		$scripts->localize('jigoshop.admin.product.variable', 'jigoshop_admin_product_variable', array(
			'ajax' => $wp->getAjaxUrl(),
		));
	}

	public function __construct(Wordpress $wp)
	{
		parent::__construct($wp);
		$this->sales = new Attributes\Sales();
	}

	/**
	 * Checks whether the product requires shipping.
	 *
	 * @return bool Whether the product requires shipping.
	 */
	public function isShippable()
	{
		return array_reduce($this->variations, function($value, $item){
			/** @var $item Shippable */
			return $value & $item->isShippable();
		}, true);
	}

	/**
	 * @return array
	 */
	public function getVariations()
	{
		return $this->variations;
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @return Attributes\Sales Current product sales data.
	 */
	public function getSales()
	{
		return $this->sales;
	}

	/**
	 * Sets product sales.
	 * Applies `jigoshop\product\set_sales` filter to allow plugins to modify sales data. When filter returns false sales are not modified at all.
	 *
	 * @param Attributes\Sales $sales Product sales data.
	 */
	public function setSales(Attributes\Sales $sales)
	{
		$sales = $this->wp->applyFilters('jigoshop\product\set_sales', $sales, $this);

		if ($sales !== false) {
			$this->sales = $sales;
			$this->dirtyFields[] = 'sales';
		}
	}

	/**
	 * @return array List of variable attributes.
	 */
	public function getVariableAttributes()
	{
		return array_filter($this->getAttributes(), function($item){
			/** @var $item Product\Attribute\Variable */
			return $item instanceof Product\Attribute\Variable && $item->isVariable();
		});
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = parent::getStateToSave();

		foreach ($this->dirtyFields as $field) {
			switch ($field) {
//				case 'regular_price':
//					$toSave['regular_price'] = $this->regularPrice;
//					break;
			}
		}

		$toSave['sales'] = $this->sales;

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		parent::restoreState($state);

		if (isset($state['sales']) && !empty($state['sales'])) {
			if (is_array($state['sales'])) {
				$this->sales->setEnabled($state['sales']['enabled'] == 'on');
				$this->sales->setFromTime($state['sales']['from']);
				$this->sales->setToTime($state['sales']['to']);
				$this->sales->setPrice($state['sales']['price']);
			} else {
				$this->sales = unserialize($state['sales']);
			}
		}
	}

	/**
	 * Marks values provided in the state as dirty.
	 *
	 * @param array $state Product state.
	 */
	public function markAsDirty(array $state)
	{
		$this->dirtyFields[] = 'sales';

		parent::markAsDirty($state);
	}

	/**
	 * @return array Minimal state to identify the product.
	 */
	public function getState()
	{
		return array(
			'type' => $this->getType(),
			'id' => $this->getId(),
		);
	}
}
