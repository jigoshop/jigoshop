<?php
/**
 * @var $attributes array List of currently available attributes.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $types array List of available attribute types.
 */
?>
<div class="wrap jigoshop">
	<h1><?php _e('Jigoshop &rang; Product &rang; Attributes', 'jigoshop'); ?></h1>
	<div class="alert alert-info"><?php _e('Every change to attributes is automatically saved.', 'jigoshop'); ?></div>
	<div id="messages">
		<?php \Jigoshop\Helper\Render::output('shop/messages', array('messages' => $messages)); ?>
	</div>
	<noscript>
		<div class="alert alert-danger" role="alert"><?php _e('<strong>Warning</strong> Attributes panel will not work properly without JavaScript.', 'jigoshop'); ?></div>
	</noscript>
	<div class="tab-content">
		<form role="form" method="POST">
			<table class="table table-condensed">
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
						<tr data-id="<?php echo $id; ?>">
							<td>
								<?php \Jigoshop\Admin\Helper\Forms::text(array(
									'name' => 'attributes['.$id.'][label]',
									'value' => $attribute['label'],
								)); ?>
							</td>
							<td>
								<?php \Jigoshop\Admin\Helper\Forms::text(array(
									'name' => 'attributes['.$id.'][slug]',
									'value' => $attribute['slug'],
								)); ?>
							</td>
							<td>
								<?php \Jigoshop\Admin\Helper\Forms::select(array(
									'name' => 'attributes['.$id.'][type]',
									'value' => $attribute['type'],
									'options' => $types,
								)); ?>
							</td>
							<td>
								<?php if (!empty($attribute['options'])): ?>
								<button type="button" class="configure-attribute btn btn-default"><?php _e('Configure', 'jigoshop'); ?></button>
								<?php endif; ?>
								<button type="button" class="remove-attribute btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
							</td>
						</tr>
						<?php if (!empty($attribute['options'])): ?>
						<tr class="options" data-id="<?php echo $id; ?>">
							<td colspan="4">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h5 class="panel-title">
											<?php _e('Attribute options', 'jigoshop'); ?>
											<button type="button" class="btn btn-default pull-right"><?php _e('Close', 'jigoshop'); ?></button>
										</h5>
									</div>
									<table class="table table-condensed">
										<thead>
										<tr>
											<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
											<th scope="col"><?php _e('Value', 'jigoshop'); ?></th>
											<th scope="col"></th>
										</tr>
										</thead>
										<tbody>
										<?php foreach($attribute['options'] as $option_id => $option): ?>
											<tr>
												<td>
													<?php \Jigoshop\Admin\Helper\Forms::text(array(
														'name' => 'attributes['.$id.'][options]['.$option_id.'][label]',
														'value' => $option['label'],
													)); ?>
												</td>
												<td>
													<?php \Jigoshop\Admin\Helper\Forms::text(array(
														'name' => 'attributes['.$id.'][options]['.$option_id.'][value]',
														'value' => $option['value'],
													)); ?>
												</td>
												<td>
													<button type="button" class="remove-attribute-option btn btn-default" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
												</td>
											</tr>
										<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</td>
						</tr>
						<?php endif; ?>
					<?php endforeach; ?>
					<tr>
						<td>
							<?php \Jigoshop\Admin\Helper\Forms::text(array(
								'name' => 'label',
								'id' => 'attribute-label',
								'placeholder' => __('New attribute label', 'jigoshop'),
							)); ?>
						</td>
						<td>
							<?php \Jigoshop\Admin\Helper\Forms::text(array(
								'name' => 'slug',
								'id' => 'attribute-slug',
								'placeholder' => __('New attribute slug', 'jigoshop'),
							)); ?>
						</td>
						<td>
							<?php \Jigoshop\Admin\Helper\Forms::select(array(
								'name' => 'type',
								'id' => 'attribute-type',
								'options' => $types,
							)); ?>
						</td>
						<td>
							<button type="button" class="btn btn-default" id="add-attribute"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
