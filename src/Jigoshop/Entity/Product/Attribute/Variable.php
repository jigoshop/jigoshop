<?php
namespace Jigoshop\Entity\Product\Attribute;

interface Variable
{
	/**
	 * @return bool Whether attribute is used for variations.
	 */
	public function isVariable();

	/**
	 * @param boolean $variable Set whether attribute is for variable products.
	 */
	public function setVariable($variable);
}
