<?php
/**
 * @var $files int Number of files in cache directory.
 */
?>
<p><?php echo sprintf(__('Files in cache: %d', 'jigoshop_web_optimization_system'), $files); ?></p>
<?php echo \Jigoshop_Forms::checkbox(array(
	'label' => __('Clear cache', 'jigoshop_web_optimization_system'),
	'name' => 'clear_cache',
	'desc' => __('This will remove all files in cache causing the plugin to recreate all data.', 'jigoshop_web_optimization_system'),
	'tip' => __('To clear cache please check the checkbox and save settings.', 'jigoshop_web_optimization_system'),
	'value' => 'on',
)); ?>