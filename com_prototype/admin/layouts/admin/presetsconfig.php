<?php
/**
 * @package    Prototype Component
 * @version    1.4.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

HTMLHelper::_('jquery.framework');
HTMLHelper::_('stylesheet', 'media/com_prototype/css/presetsconfig.min.css', array('version' => 'auto'));
HTMLHelper::_('script', 'media/com_prototype/js/presetsconfig.min.js', array('version' => 'auto'));
HTMLHelper::_('jquery.ui');
HTMLHelper::_('jquery.ui', array('sortable'));
?>

<div id="<?php echo $id; ?>" data-input-presetsconfig="" class="<?php echo $class; ?>">
	<div class="item" data-key="preset_X">
		<div class="input-prepend">
			<input type="text" data-name="<?php echo $name; ?>[preset_X][value]"
				   placeholder="<?php echo TEXT::_('COM_PROTOTYPE_PRESETS_CONFIG_VALUE'); ?>"
				   data-id="<?php echo $id; ?>_preset_X_value" class="value input-small">
		</div>
		<div class="input-prepend">
			<input type="text" data-name="<?php echo $name; ?>[preset_X][title]" class="title"
				   placeholder="<?php echo TEXT::_('COM_PROTOTYPE_PRESETS_CONFIG_TITLE'); ?>"
				   data-id="<?php echo $id; ?>_preset_X_title">
		</div>
		<div class="actions btn-group">
			<a class="remove btn btn-small button btn-danger"><span class="icon-remove"></span></a>
			<a class="move btn btn-small button btn-primary"><span class="icon-move"></span></a>
			<a class="add btn btn-small button btn-success"><span class="icon-plus-2"></span></a>
		</div>
	</div>
	<div id="<?php echo $id; ?>_result" class="result">
		<?php foreach ($value as $key => $val): ?>
			<div class="item" data-key="<?php echo $key; ?>">
				<div class="input-prepend">
					<input type="text" name="<?php echo $name; ?>[<?php echo $key; ?>][value]"
						   placeholder="<?php echo TEXT::_('COM_PROTOTYPE_PRESETS_CONFIG_VALUE'); ?>"
						   id="<?php echo $id; ?>_<?php echo $key; ?>_value" class="value input-small"
						   value="<?php echo $val['value']; ?>">
				</div>
				<div class="input-prepend">
					<input type="text" name="<?php echo $name; ?>[<?php echo $key; ?>][title]"  class="title"
						   id="<?php echo $id; ?>_<?php echo $key; ?>_title"
						   value="<?php echo !(empty($val['title'])) ? $val['title'] : ''; ?>"
						   placeholder="<?php echo TEXT::_('COM_PROTOTYPE_PRESETS_CONFIG_TITLE'); ?>">
				</div>

				<div class="actions btn-group">
					<a class="remove btn btn-small button btn-danger"><span class="icon-remove"></span></a>
					<a class="move btn btn-small button btn-primary"><span class="icon-move"></span></a>
					<a class="add btn btn-small button btn-success"><span class="icon-plus-2"></span></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>