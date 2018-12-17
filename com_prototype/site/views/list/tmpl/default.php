<?php
/**
 * @package    Prototype Component
 * @version    1.4.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

LayoutHelper::render('components.com_prototype.list.head');
?>
<div>
	<form action="<?php echo htmlspecialchars(Factory::getURI()->toString()); ?>" method="get" name="adminForm">
		<?php foreach (array_keys($this->filterForm->getGroup('filter')) as $filter): ?>
			<?php echo $this->filterForm->renderField(str_replace('filter_', '', $filter), 'filter'); ?>
		<?php endforeach; ?>
		<button type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<a href="<?php echo $this->link; ?>"><?php echo Text::_('JCLEAR'); ?></a>
	</form>
	<?php if (empty($this->items)): ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else: ?>
		<?php foreach ($this->items as $item): ?>
			<?php echo $item->render->listItem; ?>
		<?php endforeach; ?>
		<div>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
		<?php echo LayoutHelper::render('components.com_prototype.list.balloon'); ?>
		<?php echo LayoutHelper::render('components.com_prototype.list.author'); ?>
	<?php endif; ?>
</div>
