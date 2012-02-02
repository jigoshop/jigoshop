<?php defined( 'WP_UNINSTALL_PLUGIN' ) or die('No direct script access');
/**
 * Uninstall Script
 *
 * Removes all traces of Jigoshop from the wordpress database
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Core
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

// Remove the widget cache entry
delete_transient( 'jigoshop_widget_cache' );