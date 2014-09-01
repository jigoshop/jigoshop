<?php

namespace Jigoshop\Core\Types;

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
		if(!$container->hasDefinition('jigoshop.types')){
			return;
		}

		$definition = $container->getDefinition('jigoshop.types');

		$types = $container->findTaggedServiceIds('jigoshop.type.post');
		foreach($types as $id => $attributes){
			$definition->addMethodCall('addPostType', array(new Reference($id)));
		}

		$taxonomies = $container->findTaggedServiceIds('jigoshop.type.taxonomy');
		foreach($taxonomies as $id => $attributes){
			$definition->addMethodCall('addTaxonomy', array(new Reference($id)));
		}
	}
}
