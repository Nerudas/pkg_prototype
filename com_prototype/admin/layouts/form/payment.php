<?php
/**
 * @package    Prototype Component
 * @version    1.4.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

extract($displayData);
?>

<fieldset id="<?php echo $id; ?>" class="<?php echo trim($class . ' checkboxes'); ?>">
	<?php foreach ($options as $i => $option) : ?>
		<label for="<?php echo $id . '_' . $i; ?>">
			<input type="radio" id="<?php echo $id . '_' . $i; ?>" name="<?php echo $name; ?>"
				   value="<?php echo $option['value']; ?>" <?php echo $option['checked'] ? 'checked="checked"' : ''; ?>/>
			<?php echo $option['title']; ?></label>
	<?php endforeach; ?>
</fieldset>