<?php

namespace Jigoshop\Admin\Settings;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for loading all post types
 *
 * @package Jigoshop\Core\Types
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
		if(!$container->hasDefinition('jigoshop.admin.settings')){
			return;
		}

		$definition = $container->getDefinition('jigoshop.admin.settings');

		$tabs = $container->findTaggedServiceIds('jigoshop.admin.settings.tab');
		foreach($tabs as $id => $attributes){
			$definition->addMethodCall('addTab', array(new Reference($id)));
		}
	}
}
