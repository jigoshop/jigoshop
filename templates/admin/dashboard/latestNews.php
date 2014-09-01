<?php
/**
 * @var $items array List of items to display
 */
?>
<?php if (count($items) > 0): ?>
	<ul>
		<?php foreach ($items as $item): ?>
			<li><a href="<?= esc_url($item['link']); ?>"><?= $item['title']; ?></a> &ndash; <span class="rss-date"><?= $item['date']; ?></span></li>
		<?php endforeach; ?>
	</ul>
<?php else: ?>
	<p class="notice"><?= __('No items found.', 'jigoshop'); ?></p>
<?php endif; ?>