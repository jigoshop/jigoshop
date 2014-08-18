<?php

namespace Jigoshop\Web\Optimization\Asset\Minified;

use Jigoshop\Web\Optimization\Asset\Stylesheet as BaseStylesheet;

/**
 * Class representing stylesheet asset.
 *
 * @package Jigoshop\Web\Optimization
 * @author Amadeusz Starzykiewicz
 */
class Stylesheet extends BaseStylesheet
{
	protected function getAssetFilters()
	{
		return array('cssmin', 'cssrewrite');
	}
}