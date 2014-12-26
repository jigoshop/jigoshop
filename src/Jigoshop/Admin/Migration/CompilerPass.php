<?php

namespace Jigoshop\Admin\Migration;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for loading all migration tools
 *
 * @package Jigoshop\Admin\Migration
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
		if(!$container->hasDefinition('jigoshop.admin.migration')){
			return;
		}

		$definition = $container->getDefinition('jigoshop.admin.migration');
		$tools = $container->findTaggedServiceIds('jigoshop.admin.migration');

		// If no migration tools - remove tab
		if (empty($tools)) {
			$container->removeDefinition('jigoshop.admin.migration');
			return;
		}

		foreach($tools as $id => $attributes){
			$definition->addMethodCall('addTool', array(new Reference($id)));
		}
	}
}
