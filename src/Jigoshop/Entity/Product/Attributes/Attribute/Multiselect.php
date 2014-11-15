<?php

namespace Jigoshop\Entity\Product\Attributes\Attribute;

use Jigoshop\Entity\Product\Attributes\Attribute;

class Multiselect extends Attribute
{
	const TYPE = 0;

	/**
	 * @return int Type of attribute.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @param mixed $value New value for attribute.
	 */
	public function setValue($value)
	{
		$this->value = array_filter(explode('|', $value));
	}
}
