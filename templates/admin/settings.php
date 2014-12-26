<?php
use Jigoshop\Admin\Settings;
use Jigoshop\Helper\Render;

/**
 * @var $tabs array List of tabs to display.
 * @var $current_tab string Current tab slug.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>
<div class="wrap jigoshop">
	<h1><?php _e('Jigoshop &rang; Settings', 'jigoshop'); ?></h1>
	<?php settings_errors(); ?>
	<?php Render::output('shop/messages', array('messages' => $messages)); ?>
	<ul class="nav nav-tabs nav-justified" role="tablist">
		<?php foreach($tabs as $tab): /** @var $tab \Jigoshop\Admin\Settings\TabInterface */ ?>
		<li class="<?php $tab->getSlug() == $current_tab and print 'active'; ?>">
			<a href="?page=<?php echo Settings::NAME; ?>&tab=<?php echo $tab->getSlug(); ?>"><?php echo $tab->getTitle(); ?></a>
		</li>
		<?php endforeach; ?>
	</ul>
	<noscript>
		<div class="alert alert-danger" role="alert"><?php _e('<strong>Warning</strong> Options panel will not work properly without JavaScript.', 'jigoshop'); ?></div>
	</noscript>
	<div class="tab-content">
		<div class="tab-pane active">
			<form action="options.php" id="jigoshop" method="post" enctype="multipart/form-data" role="form" class="clearfix">
				<input type="hidden" name="tab" value="<?php echo $current_tab; ?>" />
				<?php settings_fields(Settings::NAME); ?>
				<?php do_settings_sections(Settings::NAME); ?>
				<button type="submit" class="btn btn-primary pull-right"><?php echo __('Save changes', 'jigoshop'); ?></button>
			</form>
		</div>
	</div>
</div>
