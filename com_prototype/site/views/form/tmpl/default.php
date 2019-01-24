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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$app = Factory::getApplication();
$doc = Factory::getDocument();

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');

$doc->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "item.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<form action="<?php echo Route::_(PrototypeHelperRoute::getFormRoute($this->item->id, $this->category->id, $app->input->getCmd('return_view'))); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<?php if (!empty($this->presets)): ?>
		<?php echo LayoutHelper::render('components.com_prototype.form.presets',
			array('form' => $this->form, 'presets' => $this->presets)); ?>
	<?php endif; ?>
	<div data-prototype-form="form" <?php echo (!empty($this->presets)) ? 'style="display: none"' : ''; ?>>
		<?php if (!empty($this->presets)): ?>
			<div>
				<span data-preset-title="label"></span>
				<a data-prototype-form="change-preset">
					<?php echo Text::_('COM_PROTOTYPE_PRESETS_CHANGE'); ?>
				</a>
			</div>
		<?php endif; ?>
		<?php echo $this->form->renderFieldSet('main'); ?>
		<?php echo $this->form->renderFieldSet('hidden'); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo $app->input->getCmd('return'); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
		<button onclick="Joomla.submitbutton('item.save');"><?php echo Text::_('JAPPLY'); ?></button>
		<button onclick="Joomla.submitbutton('item.cancel');"><?php echo Text::_('JCANCEL'); ?></button>
	</div>
</form>