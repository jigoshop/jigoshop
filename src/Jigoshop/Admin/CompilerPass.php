<?php

namespace Jigoshop\Admin;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for loading all post types
 *
 * @package Jigoshop\Admin
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
		if(!$container->hasDefinition('jigoshop.admin')){
			return;
		}

		$definition = $container->getDefinition('jigoshop.admin');

		$pages = $container->findTaggedServiceIds('jigoshop.admin.page');
		foreach($pages as $id => $attributes){
			$definition->addMethodCall('addPage', array(new Reference($id)));
		}
	}
}
