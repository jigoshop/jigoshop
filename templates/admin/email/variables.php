<?php
/**
 * @var $email \Jigoshop\Entity\Email The email.
 * @var $emails array List of available emails.
 */
?>
<div id="available_arguments">
	<table class="table">
		<?php
		$i = 0;
		$selected = $email->getActions();
		if (!empty($selected[0]) && !empty($emails[$selected[0]])) {
			$keys = array_keys($emails[$selected[0]]['arguments']);
			if (count($selected) > 1) {
				foreach ($selected as $hook) {
					$keys = array_intersect(array_keys($emails[$hook]['arguments']), $keys);
				}
			}
			asort($keys);
			if(empty($keys)){
				_e('Selected actions have not common variables', 'jigoshop');
			}
			foreach ($keys as $key) : ?>
				<?php if($i % 3 == 0): ?> <tr> <?php endif; ?>
				<td width="33.33%"><strong>[<?php echo $key ?>] </strong> - <?php echo $emails[$selected[0]]['arguments'][$key] ?> <br/>
				<?php if($i % 3 == 2): ?> </tr> <?php endif; ?>
				<?php $i++; ?>
			<?php endforeach;
		} else {
			_e('Select email action to see available variables', 'jigoshop');
		}?>
	</table>
</div>
