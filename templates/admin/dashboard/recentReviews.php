<?php
/**
 * @var $comments array List of product reviews.
 */
?>
<div class="inside jigoshop-reviews-widget">
	<?php if (is_array($comments) && count($comments) > 0): ?>
		<ul>
			<?php foreach ($comments as $comment): $rating = get_comment_meta($comment->comment_ID, 'rating', true); ?>
				<li>
					<?php echo get_avatar($comment->comment_author, '32'); ?>
					<div class="star-rating" title="<?php echo esc_attr($rating); ?>">
						<span style="width:<?php echo ($rating * 16); ?>px"><?php echo $rating.' '.__('out of 5', 'jigoshop'); ?></span>
					</div>
					<h4 class="meta"><a
							href="<?php echo get_permalink($comment->ID); ?>#comment-<?php echo $comment->comment_ID; ?>"><?php echo $comment->post_title; ?></a><?php echo __(' reviewed by ', 'jigoshop'); ?> <?php echo strip_tags($comment->comment_author); ?>
					</h4>
					<blockquote><?php echo strip_tags($comment->comment_excerpt); ?> [...]</blockquote>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<p><?php echo __('There are no product reviews yet.', 'jigoshop'); ?></p>
	<?php endif; ?>
</div>
