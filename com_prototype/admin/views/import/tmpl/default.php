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


HTMLHelper::_('script', 'media/com_prototype/js/import.min.js', array('version' => 'auto'));
?>

<div>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<form action="<?php echo Route::_('index.php?option=com_prototype&view=import'); ?>"
			  method="post" id="importForm" class="form-horizontal">

			<div class="control-group info">
				<div >
					<a href="https://convertio.co/ru/xlsx-converter/" target="_blank" class="btn">Excel to cvs
						convector</a>
					<a href="/administrator/components/com_prototype/views/import/tmpl/blank.xlsx" class="btn">
						blank.xlsx
					</a>
					<a href="/administrator/components/com_prototype/views/import/tmpl/demo.xlsx" class="btn">
						demo.xlsx
					</a>
				</div>
			</div>

			<div class="control-group file">
				<div class="control-label">
					<?php echo Text::_('COM_PROTOTYPE_IMPORT_FILE'); ?>
				</div>
				<div class="controls">
					<input type="file" name="file" accept="text/csv" value="">
				</div>
			</div>
			<div class="control-group run">
				<div class="controls">
					<button class="btn btn-success" type="submit">
						<?php echo Text::_('COM_PROTOTYPE_IMPORT_RUN'); ?>
					</button>
				</div>
			</div>
			<div class="control-group stages">
				<div class="control-label">
					<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGES'); ?>
				</div>
				<div class="controls">
					<ul class="stages unstyled">
						<li class="stage" data-stage="1">
							<div class="title">
								<strong><?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_1'); ?></strong>
							</div>
							<div class="result">
								<div class="error text-error">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_ERROR'); ?>
								</div>
								<div class="success text-success">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_SUCCESS'); ?>
								</div>
								<div class="status">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_STATUS'); ?>
									<span class="current">0</span>/<span class="total">1</span>
								</div>
							</div>
						</li>
						<li class="stage" data-stage="2">
							<div class="title">
								<strong><?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_2'); ?></strong>
							</div>
							<div class="result">
								<div class="error text-error">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_ERROR'); ?>
								</div>
								<div class="success text-success">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_SUCCESS'); ?>
								</div>
								<div class="status">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_STATUS'); ?>
									<span class="current">0</span>/<span class="total"></span>
								</div>
							</div>
						</li>
						<li class="stage" data-stage="3">
							<div class="title">
								<strong><?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_3'); ?></strong>
							</div>
							<div class="result">
								<div class="error text-error">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_ERROR'); ?>
								</div>
								<div class="success text-success">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_SUCCESS'); ?>
								</div>
								<div class="status">
									<?php echo Text::_('COM_PROTOTYPE_IMPORT_STAGE_STATUS'); ?>
									<span class="current">0</span>/<span class="total">1</span>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</form>
	</div>
</div>
