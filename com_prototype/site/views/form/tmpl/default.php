<?php
/**
 * @package    Prototype Component
 * @version    1.0.3
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
<form action="<?php echo Route::_(PrototypeHelperRoute::getFormRoute(
	$this->item->id,
	$this->category->id,
	$app->input->getCmd('return_view'))); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<?php echo $this->form->renderField('title'); ?>


	<?php echo $this->form->renderField('map'); ?>
	<?php echo $this->form->renderField('images'); ?>

	<?php echo $this->form->renderFieldSet('extra'); ?>

	<?php echo $this->form->renderFieldSet('hidden'); ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo $app->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>

	<button onclick="Joomla.submitbutton('item.save');"><?php echo Text::_('JAPPLY'); ?></button>
	<button onclick="Joomla.submitbutton('item.cancel');"><?php echo Text::_('JCANCEL'); ?></button>
</form>