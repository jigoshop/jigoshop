<?php

namespace Jigoshop\Web\Optimization\Asset;

/**
 * Class representing stylesheet asset.
 *
 * @package Jigoshop\Web\Optimization
 * @author Amadeusz Starzykiewicz
 */
class Stylesheet extends Asset
{
	protected function getNamePattern()
	{
		return 'styles_{location}_{page}.css';
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