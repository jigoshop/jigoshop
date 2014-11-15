<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Render;

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
		<?php Render::output('shop/messages', array('messages' => $messages)); ?>
	</div>
	<noscript>
		<div class="alert alert-danger" role="alert"><?php _e('<strong>Warning</strong> Attributes panel will not work properly without JavaScript.', 'jigoshop'); ?></div>
	</noscript>
	<div class="tab-content">
		<form role="form" method="POST">
			<table class="table table-condensed" id="product-attributes">
				<thead>
					<tr>
						<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
						<th scope="col"><?php _e('Slug', 'jigoshop'); ?></th>
						<th scope="col"><?php _e('Type', 'jigoshop'); ?></th>
						<th scope="col"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($attributes as $attribute): ?>
						<?php Render::output('admin/product_attributes/attribute', array('attribute' => $attribute, 'id' => $attribute->getId(), 'types' => $types)); ?>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td>
							<?php Forms::text(array(
								'name' => 'label',
								'id' => 'attribute-label',
								'placeholder' => __('New attribute label', 'jigoshop'),
							)); ?>
						</td>
						<td>
							<?php Forms::text(array(
								'name' => 'slug',
								'id' => 'attribute-slug',
								'placeholder' => __('New attribute slug', 'jigoshop'),
							)); ?>
						</td>
						<td>
							<?php Forms::select(array(
								'name' => 'type',
								'id' => 'attribute-type',
								'options' => $types,
							)); ?>
						</td>
						<td>
							<button type="button" class="btn btn-default" id="add-attribute"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
						</td>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>
