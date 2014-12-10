<?php

namespace Jigoshop\Core\Installer;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for loading all installation requirements.
 *
 * @package Jigoshop\Core\Installer
 * @author Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject post types and taxonomies into Types instance.
	 *
	 * @param ContainerBuilder $container
	 * @api
	 */
	public function process(ContainerBuilder $container)
	{
		if(!$container->hasDefinition('jigoshop.installer')){
			return;
		}

		$definition = $container->getDefinition('jigoshop.installer');

		$types = $container->findTaggedServiceIds('jigoshop.installer');
		foreach($types as $id => $attributes){
			$definition->addMethodCall('addInitializer', array(new Reference($id)));
		}
	}
}
