<?php
/**
 * @package    Prototype Component
 * @version    1.3.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

extract($displayData);

HTMLHelper::_('jquery.framework');
HTMLHelper::_('stylesheet', 'media/com_prototype/css/presetpalcemark.min.css', array('version' => 'auto'));
HTMLHelper::_('script', 'media/com_prototype/js/presetpalcemark.min.js', array('version' => 'auto'));

$filename = '';
if (!empty($value)) {
	$filename = str_replace('.'.File::getExt($value), '', File::getName($value));
}

?>

<div id="<?php echo $id; ?>" class="<?php echo $class; ?>" data-input-preset-palcemark="<?php echo $id; ?>"
	 data-filename="<?php echo $filename; ?>">
	<div class="form">
		<div class="action preview">
			<i class="icon-eye"></i>
			<div class="preview-modal">
				<img src="/<?php echo $value; ?>"/>
			</div>
		</div>
		<input type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>_value" value="<?php echo $value; ?>"
			   class="value" readonly="readonly">
		<input id="<?php echo $id; ?>_field" class="file" type="file" accept="image/*"/>
		<label for="<?php echo $id; ?>_field" class="action select btn hasTooltip"
			   title="<?php echo Text::_('JLIB_FORM_BUTTON_SELECT'); ?>">
			<?php echo Text::_('JLIB_FORM_BUTTON_SELECT'); ?>
		</label>
		<a class="action remove btn icon-remove"></a>
	</div>
</div>