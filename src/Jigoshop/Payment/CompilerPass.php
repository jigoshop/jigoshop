<?php

namespace Jigoshop\Payment;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for loading all shipping methods
 *
 * @package Jigoshop\Payment
 * @author Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject payment methods into payment service.
	 *
	 * @param ContainerBuilder $container
	 * @api
	 */
	public function process(ContainerBuilder $container)
	{
		if(!$container->hasDefinition('jigoshop.service.payment')){
			return;
		}

		$definition = $container->getDefinition('jigoshop.service.payment');

		$methods = $container->findTaggedServiceIds('jigoshop.payment.method');
		foreach($methods as $id => $attributes){
			$definition->addMethodCall('addMethod', array(new Reference($id)));
		}
	}
}
