<?php
/**
 * @var $comments array List of product reviews.
 * @var $rating int Rating.
 */
?>
<div class="inside jigoshop-reviews-widget">
	<?php if (is_array($comments) && count($comments) > 0): ?>
		<ul>
			<?php foreach ($comments as $comment): $rating = get_comment_meta($comment->comment_ID, 'rating', true); ?>
				<li>
					<?= get_avatar($comment->comment_author, '32'); ?>
					<div class="star-rating" title="<?= esc_attr($rating); ?>">
						<span style="width:<?= ($rating * 16); ?>px"><?= $rating.' '.__('out of 5', 'jigoshop'); ?></span>
					</div>
					<h4 class="meta"><a
							href="<?= get_permalink($comment->ID); ?>#comment-<?= $comment->comment_ID; ?>"><?= $comment->post_title; ?></a><?= __(' reviewed by ', 'jigoshop'); ?> <?= strip_tags($comment->comment_author); ?>
					</h4>
					<blockquote><?= strip_tags($comment->comment_excerpt); ?> [...]</blockquote>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<p><?= __('There are no product reviews yet.', 'jigoshop'); ?></p>
	<?php endif; ?>
</div>