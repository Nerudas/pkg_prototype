<?php
/**
 * @package    Prototype Component
 * @version    1.3.6
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

extract($displayData);

$filename = '';
if (!empty($value))
{
	$filename = str_replace('.' . File::getExt($value), '', File::getName($value));
}

?>

<div id="<?php echo $id; ?>" class="<?php echo $class; ?>" data-input-preset-icon="<?php echo $id; ?>"
	 data-filename="<?php echo $filename; ?>">
	<div class="form">
		<img src="/<?php echo $value; ?>"/>
		<input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>_value" value="<?php echo $value; ?>"
			   class="value" readonly="readonly">
		<input id="<?php echo $id; ?>_field" class="file" type="file" accept="image/*"/>
		<div class="actions">
			<label for="<?php echo $id; ?>_field" class="action select btn hasTooltip"
				   title="<?php echo Text::_('JLIB_FORM_BUTTON_SELECT'); ?>">
				<?php echo Text::_('JLIB_FORM_BUTTON_SELECT'); ?>
			</label><a class="action remove btn icon-remove"></a>
		</div>
	</div>
</div>