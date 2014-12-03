<?php

namespace Jigoshop\Core;

use WPAL\Wordpress;

/**
 * Roles
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Roles
{
	/** @var \WPAL\Wordpress */
	private $wp;

	/**
	 * Initializes required capabilities.
	 * Supports 3 filters:
	 *  * jigoshop\role\customer - customer role capabilities array
	 *  * jigoshop\role\shop_manager - shop manager role capabilities array
	 *  * jigoshop\capability\types - capabilities for custom types
	 *
	 * @param Wordpress $wp
	 */
	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$roles = $wp->getRoles();

		// Customer role
		$wp->addRole('customer', __('Customer', 'jigoshop'), $wp->applyFilters('jigoshop\role\customer', array(
			'read' => true,
			'edit_posts' => false,
			'delete_posts' => false
		)));

		// Shop manager role
		$wp->addRole('shop_manager', __('Shop Manager', 'jigoshop'), $wp->applyFilters('jigoshop\role\shop_manager', array(
			'read' => true,
			'read_private_pages' => true,
			'read_private_posts' => true,
			'edit_users' => true,
			'edit_posts' => true,
			'edit_pages' => true,
			'edit_published_posts' => true,
			'edit_published_pages' => true,
			'edit_private_pages' => true,
			'edit_private_posts' => true,
			'edit_others_posts' => true,
			'edit_others_pages' => true,
			'publish_posts' => true,
			'publish_pages' => true,
			'delete_posts' => true,
			'delete_pages' => true,
			'delete_private_pages' => true,
			'delete_private_posts' => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'delete_others_posts' => true,
			'delete_others_pages' => true,
			'manage_categories' => true,
			'manage_links' => true,
			'moderate_comments' => true,
			'unfiltered_html' => true,
			'upload_files' => true,
			'export' => true,
			'import' => true,
		)));

		foreach ($this->getCapabilities() as $group) {
			foreach ($group as $cap) {
				$roles->add_cap('administrator', $cap);
				$roles->add_cap('shop_manager', $cap);
			}
		}
	}

	private function getCapabilities()
	{
		$capabilities = array(
			'core' => array(
				'manage_jigoshop',
				'view_jigoshop_reports',
				'manage_jigoshop_orders',
				'manage_jigoshop_coupons',
				'manage_jigoshop_products'
			)
		);

		$types = $this->wp->applyFilters('jigoshop\capability\types', array(Types::PRODUCT, Types::ORDER, Types::EMAIL)); // TODO: Add coupons
		foreach ($types as $type) {
			$capabilities[$type] = array(
				// Post type
				"edit_{$type}",
				"read_{$type}",
				"delete_{$type}",
				"edit_{$type}s",
				"edit_others_{$type}s",
				"publish_{$type}s",
				"read_private_{$type}s",
				"delete_{$type}s",
				"delete_private_{$type}s",
				"delete_published_{$type}s",
				"delete_others_{$type}s",
				"edit_private_{$type}s",
				"edit_published_{$type}s",
				// Terms
				"manage_{$type}_terms",
				"edit_{$type}_terms",
				"delete_{$type}_terms",
				"assign_{$type}_terms"
			);
		}

		return $capabilities;
	}
}
