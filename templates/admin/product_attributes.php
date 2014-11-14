<?php
/**
 * @var $attributes array List of currently available attributes.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>
<div class="wrap jigoshop">
	<h1><?php _e('Jigoshop &rang; Product &rang; Attributes', 'jigoshop'); ?></h1>
	<div id="messages">
		<?php \Jigoshop\Helper\Render::output('shop/messages', array('messages' => $messages)); ?>
	</div>
	<noscript>
		<div class="alert alert-danger" role="alert"><?php _e('<strong>Warning</strong> Attributes panel will not work properly without JavaScript.', 'jigoshop'); ?></div>
	</noscript>
	<div class="tab-content">
		<table class="table">
			<thead>
				<tr>
					<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
					<th scope="col"><?php _e('Slug', 'jigoshop'); ?></th>
					<th scope="col"><?php _e('Type', 'jigoshop'); ?></th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($attributes as $id => $attribute): ?>
					<tr>
						<td><?php echo $attribute['label']; ?></td>
						<td><?php echo $attribute['slug']; ?></td>
						<td><?php echo $attribute['type']; ?></td>
						<td>
							<button type="button" class="remove-attribute btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td>
						<?php \Jigoshop\Admin\Helper\Forms::text(array(
							'name' => 'attribute[label]',
							'placeholder' => __('New attribute label', 'jigoshop'),
						)); ?>
					</td>
					<td>
						<?php \Jigoshop\Admin\Helper\Forms::text(array(
							'name' => 'attribute[slug]',
							'placeholder' => __('New attribute slug', 'jigoshop'),
						)); ?>
					</td>
					<td>
						<?php \Jigoshop\Admin\Helper\Forms::select(array(
							'name' => 'attribute[type]',
							'options' => array(
								'multiselect' => __('Multiselect', 'jigoshop'),
								'select' => __('Select', 'jigoshop'),
								'text' => __('Text', 'jigoshop'),
							)
						)); ?>
					</td>
					<td>
						<button type="button" class="btn btn-default" id="add-attribute"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
