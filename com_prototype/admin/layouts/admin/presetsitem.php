<?php
/**
 * @package    Prototype Component
 * @version    1.3.5
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

?>
<select name="<?php echo $name; ?>" id="<?php echo $id; ?>" <?php echo ($required) ? 'required' : ''; ?>>
	<option value="" <?php echo (empty($value)) ? 'selected' : ''; ?> >
		<?php echo Text::_('COM_PROTOTYPE_PRESETS_SELECT'); ?>
	</option>
	<?php foreach ($presets as $preset): ?>
		<option value="<?php echo $preset['key']; ?>" <?php echo (!empty($value) && $preset['key'] == $value) ? 'selected' : ''; ?>
				data-preset-price="<?php echo $preset['price']; ?>"
				data-preset-price_title="<?php echo $preset['price_title']; ?>"
				data-preset-delivery="<?php echo $preset['delivery']; ?>"
				data-preset-delivery_title="<?php echo $preset['delivery_title']; ?>"
				data-preset-object="<?php echo $preset['object']; ?>"
				data-preset-object_title="<?php echo $preset['object_title']; ?>">
			<?php echo $preset['title']; ?>
		</option>
	<?php endforeach; ?>
</select>
