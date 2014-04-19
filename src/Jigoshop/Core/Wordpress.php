<?php

namespace Jigoshop\Core;

/**
 * Provides abstraction for WordPress calls.
 *
 * @package Jigoshop\Core
 * @author Jigoshop
 */
class Wordpress
{
	/**
	 * @var \wpdb
	 */
	private $wpdb;

	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function getWPDB()
	{
		return $this->wpdb;
	}

	public function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		add_action($tag, $function_to_add, $priority, $accepted_args);
	}

	public function removeAction($tag, $function_to_remove, $priority = 10)
	{
		remove_action($tag, $function_to_remove, $priority);
	}

	public function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		add_filter($tag, $function_to_add, $priority, $accepted_args);
	}

	public function removeFilter($tag, $function_to_remove, $priority = 10)
	{
		remove_filter($tag, $function_to_remove, $priority);
	}

	public function clearScheduledHook($hook, $args = array())
	{
		wp_clear_scheduled_hook($hook, $args);
	}

	public function nextScheduled($hook, $args = array())
	{
		return wp_next_scheduled($hook, $args);
	}

	public function scheduleEvent($timestamp, $recurrence, $hook, $args = array())
	{
		wp_schedule_event($timestamp, $recurrence, $hook, $args);
	}
}