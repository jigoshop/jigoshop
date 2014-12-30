<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $value mixed Option value.
 * @var $label string Option label.
 * @var $disabled boolean Whether item is disabled.
 * @var $current mixed Currently selected value(s).
 */
var_dump($label);
?>
<option value="<?php echo $value; ?>" <?php echo Forms::selected($value, $current); ?> <?php echo Forms::disabled($disabled); ?>><?php echo $label; ?></option>
