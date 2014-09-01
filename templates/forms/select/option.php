<?php
use Jigoshop\Helper\Forms;

/**
 * @var $value mixed Option value.
 * @var $label string Option label.
 * @var $current mixed Currently selected value(s).
 */
?>
<option value="<?php echo $value; ?>" <?php echo Forms::selected($value, $current); ?>><?php echo $label; ?></option>
