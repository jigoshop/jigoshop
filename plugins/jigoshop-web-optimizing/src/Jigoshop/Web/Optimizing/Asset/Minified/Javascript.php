<?php

namespace Jigoshop\Web\Optimizing\Asset\Minified;

use Jigoshop\Web\Optimizing\Asset\Javascript as BaseJavascript;

/**
 * Class representing JavaScript asset with minification.
 *
 * @package Jigoshop\Web\Optimizing
 */
class Javascript extends BaseJavascript
{
	protected function getAssetFilters()
	{
		return array('jsmin');
	}
}