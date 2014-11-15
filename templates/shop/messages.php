<?php
/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>
<div id="messages">
	<?php foreach ($messages->getErrors() as $error): ?>
		<div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
	<?php endforeach; ?>
	<?php foreach ($messages->getWarnings() as $warning): ?>
		<div class="alert alert-warning" role="alert"><?php echo $warning; ?></div>
	<?php endforeach; ?>
	<?php foreach ($messages->getNotices() as $notice): ?>
		<div class="alert alert-success" role="alert"><?php echo $notice; ?></div>
	<?php endforeach; ?>
</div>
