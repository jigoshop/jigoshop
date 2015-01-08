<?php
/**
 * @var $items array List of items to display
 */
?>
<?php if (count($items) > 0): ?>
	<ul>
		<?php foreach ($items as $item): ?>
			<li><a href="<?php echo esc_url($item['link']); ?>"><?php echo $item['title']; ?></a> &ndash; <span class="rss-date"><?php echo $item['date']; ?></span></li>
		<?php endforeach; ?>
	</ul>
<?php else: ?>
	<p class="notice"><?php echo __('No items found.', 'jigoshop'); ?></p>
<?php endif; ?>
