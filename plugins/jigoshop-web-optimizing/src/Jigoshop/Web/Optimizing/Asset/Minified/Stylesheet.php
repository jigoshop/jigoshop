<?php

namespace Jigoshop\Web\Optimizing\Asset\Minified;

use Jigoshop\Web\Optimizing\Asset\Stylesheet as BaseStylesheet;

/**
 * Class representing stylesheet asset.
 *
 * @package Jigoshop\Web\Optimizing
 */
class Stylesheet extends BaseStylesheet
{
	protected function getAssetFilters()
	{
		return array('cssmin', 'cssrewrite');
	}
}