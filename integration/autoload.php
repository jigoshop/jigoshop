<?php

$loader = new \Symfony\Component\ClassLoader\MapClassLoader(array(
	'Jigoshop_Options_Interface' => JIGOSHOP_DIR.'/integration/classes/jigoshop_options_interface.php',
	'Jigoshop_Base' => JIGOSHOP_DIR.'/integration/classes/abstract/jigoshop_base.class.php',
	'Jigoshop_Singleton' => JIGOSHOP_DIR.'/integration/classes/abstract/jigoshop_singleton.class.php',
	'jigoshop' => JIGOSHOP_DIR.'/integration/classes/jigoshop.class.php',
	'jigoshop_countries' => JIGOSHOP_DIR.'/integration/classes/jigoshop_countries.class.php',
	'jigoshop_request_api' => JIGOSHOP_DIR.'/integration/classes/jigoshop_request_api.class.php',
	'jigoshop_payment_gateway' => JIGOSHOP_DIR.'/integration/gateways/gateway.class.php',
	'jigoshop_payment_gateways' => JIGOSHOP_DIR.'/integration/gateways/gateways.class.php',
	'jigoshop_shipping_method' => JIGOSHOP_DIR.'/integration/shipping/shipping_method.class.php',
));
$loader->register();
