<?php

namespace Jigoshop\ContainerAware;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
		if(!$container->hasDefinition('di')){
			return;
		}

		$services = $container->findTaggedServiceIds('jigoshop.container_aware');
		foreach($services as $service){
			/** @var $service Definition */
			$service->addMethodCall('setContainer', array(new Reference('di')));
		}
	}
}
