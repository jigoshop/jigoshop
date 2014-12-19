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

function jigoshop_add_script($handle, $src, array $dependencies = array(), array $options = array())
{
	$scripts = \Jigoshop\Integration::getScripts();
	$scripts->add($handle, $src, $dependencies, $options);
}

function jigoshop_remove_script($handle, array $options = array())
{
	$scripts = \Jigoshop\Integration::getScripts();
	$scripts->remove($handle, $options);
}

function jigoshop_localize_script($handle, $object, array $values)
{
	$scripts = \Jigoshop\Integration::getScripts();
	$scripts->localize($handle, $object, $values);
}

function jigoshop_add_style($handle, $src, array $dependencies = array(), array $options = array())
{
	$styles = \Jigoshop\Integration::getStyles();
	$styles->add($handle, $src, $dependencies, $options);
}

function jigoshop_remove_style($handle, array $options = array())
{
	$styles = \Jigoshop\Integration::getStyles();
	$styles->remove($handle, $options);
}
