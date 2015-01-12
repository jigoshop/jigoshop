<?php

namespace Jigoshop\Entity\Product\Attribute;

/**
 * Class Custom
 *
 * @package Jigoshop\Entity\Product\Attribute
 */
class Custom extends Text
{
	public function __construct($exists = false)
	{
		parent::__construct($exists);
		$this->setLocal(true);
	}
}
