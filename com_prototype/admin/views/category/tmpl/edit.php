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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$app = Factory::getApplication();
$doc = Factory::getDocument();

HTMLHelper::_('jquery.framework');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::stylesheet('media/com_prototype/css/admin-category.min.css', array('version' => 'auto'));
HTMLHelper::_('script', 'media/com_prototype/js/admin-category.min.js', array('version' => 'auto'));

$doc->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "category.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<style>
	#jform_map,
	#jform_map > .form {
		width: 997px !important;
		height: 345px !important;
	}
</style>
<form action="<?php echo Route::_('index.php?option=com_prototype&view=categories&id=' . $this->item->id); ?>"
	  method="post"
	  name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<div class="row-fluid form-horizontal-desktop">
						<div class="span3">
							<h4><?php echo Text::_('COM_PROTOTYPE_CATEGORY_ICON'); ?></h4>
							<?php echo $this->form->getInput('icon'); ?>

						</div>
						<div class="span9">
							<div class="control-group">
								<h4><?php echo Text::_('COM_PROTOTYPE_CATEGORY_MAP_IMAGE'); ?></h4>
								<?php echo $this->form->getInput('map'); ?>
							</div>
							<div class="control-group">
								<h4><?php echo Text::_('JTAG'); ?></h4>
								<?php echo $this->form->getInput('tags'); ?>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="span3">
				<fieldset class="form-vertical">
					<?php echo $this->form->renderFieldset('global'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'presets', Text::_('COM_PROTOTYPE_PRESETS'));
		?>
		<div class="row-fluid">
			<div class="span9">
				<?php echo $this->form->getInput('presets'); ?>
			</div>
			<div class="span3">
				<?php echo $this->form->getInput('placemark_demo'); ?>
				<?php echo $this->form->getInput('preset_demo'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php
		echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'attribs', Text::_('JGLOBAL_FIELDSET_OPTIONS'));
		$attribs = $this->form->renderFieldSet('attribs');
		$attribs = str_replace('jform[attribs][front_created]', 'jform[front_created]', $attribs);
		echo $attribs;
		echo HTMLHelper::_('bootstrap.endTab');
		?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo $this->form->renderFieldSet('metadata'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo $app->input->getCmd('return'); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>