<?php

function jigowatt_clean($var)
{
	return strip_tags(stripslashes(trim($var)));
}

function jigoshop_price($price)
{
	return \Jigoshop\Helper\Product::formatPrice($price);
}
