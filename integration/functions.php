<?php

function jigowatt_clean($var)
{
	return strip_tags(stripslashes(trim($var)));
}

function jigoshop_price($price)
{
	return \Jigoshop\Helper\Product::formatPrice($price);
}

function jigoshop_is_minumum_version($version)
{
	return jigoshop_is_minimum_version($version);
}

function jigoshop_is_minimum_version($version)
{
	return \Jigoshop\isMinimumVersion($version);
}

function jigoshop_add_required_version_notice($source, $version)
{
	return \Jigoshop\addRequiredVersionNotice($source, $version);
}
