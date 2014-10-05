<?php

namespace Jigoshop\Shipping;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for loading all shipping methods
 *
 * @package Jigoshop\Shipping
 * @author Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject shipping methods into shipping service.
	 *
	 * @param ContainerBuilder $container
	 * @api
	 */
	public function process(ContainerBuilder $container)
	{
		if(!$container->hasDefinition('jigoshop.service.shipping')){
			return;
		}

		$definition = $container->getDefinition('jigoshop.service.shipping');

		$methods = $container->findTaggedServiceIds('jigoshop.shipping.required_method');
		foreach($methods as $id => $attributes){
			$definition->addMethodCall('addMethod', array(new Reference($id)));
		}
	}
}
