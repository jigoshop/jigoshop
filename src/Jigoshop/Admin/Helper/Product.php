<?php

namespace Jigoshop\Admin\Helper;

use Jigoshop\Entity\Product as ProductEntity;

class Product
{
	public static function getSelectOption(array $options, $emptyItem = false)
	{
		$options = array_map(function($item){
			/** @var $item ProductEntity\Attribute\Option */
			return $item->getLabel();
		}, $options);

		if ($emptyItem !== false && !empty($emptyItem)) {
			array_unshift($options, $emptyItem);
		}

		return $options;
	}
}
