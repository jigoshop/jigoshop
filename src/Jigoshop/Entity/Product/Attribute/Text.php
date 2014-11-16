<?php

namespace Jigoshop\Entity\Product\Attribute;

use Jigoshop\Entity\Product\Attribute;

class Text extends Attribute
{
	const TYPE = 2;

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
		$this->value = $value;
	}

	/**
	 * @return string Value of attribute to be printed.
	 */
	public function printValue()
	{
		return $this->value;
	}
}
