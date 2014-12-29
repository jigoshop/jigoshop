<?php
use Jigoshop\Helper\Render;

/**
 * @var $tools array List of tools to display.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>
<div class="wrap jigoshop">
	<h1><?php _e('Jigoshop &rang; Migration tool', 'jigoshop'); ?></h1>
	<?php settings_errors(); ?>
	<?php Render::output('shop/messages', array('messages' => $messages)); ?>
	<p class="alert alert-info"><?php _e('This panel allows you to update your old Jigoshop plugin data to new format.', 'jigoshop'); ?></p>
	<p class="alert alert-info"><?php _e('Migration is a lengthy process and depends on how much items you have in your store. Please keep patient until the process is finished.', 'jigoshop'); ?></p>
	<p class="alert alert-danger"><?php printf(__('Please create a backup of database in case of any error! <a href="%s">Here you can find instruction how to do this</a>', 'jigoshop'), 'http://codex.wordpress.org/Backing_Up_Your_Database'); ?></p>
	<form action="" method="get">
		<ul class="list-group clearfix">
			<?php foreach ($tools as $tool): /** @var $tool \Jigoshop\Admin\Migration\Tool */ ?>
				<li class="list-group-item tool-<?php echo $tool->getId(); ?>"><?php echo $tool->display(); ?></li>
			<?php endforeach; ?>
		</ul>
		<input type="hidden" name="page" value="<?php echo Jigoshop\Admin\Migration::NAME; ?>" />
	</form>
</div>
