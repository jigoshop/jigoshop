<?php

namespace Jigoshop\Web\Optimization\Asset\Minified;

use Jigoshop\Web\Optimization\Asset\Javascript as BaseJavascript;

/**
 * Class representing JavaScript asset with minification.
 *
 * @package Jigoshop\Web\Optimization
 * @author Amadeusz Starzykiewicz
 */
class Javascript extends BaseJavascript
{
	protected function getAssetFilters()
	{
		return array('jsmin');
	}
}