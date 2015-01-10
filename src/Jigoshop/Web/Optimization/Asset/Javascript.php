<?php

namespace Jigoshop\Web\Optimization\Asset;

/**
 * Class representing JavaScript asset.
 *
 * @package Jigoshop\Web\Optimization
 * @author Amadeusz Starzykiewicz
 */
class Javascript extends Asset
{
	protected function getNamePattern()
	{
		return 'scripts_{location}_{page}.js';
	}

	protected function getAssetVariables()
	{
		return array('location', 'page');
	}

	protected function getAssetFilters()
	{
		return array();
	}
}