<?php
/**
 * @package    Prototype Component
 * @version    1.3.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

LayoutHelper::render('components.com_prototype.map.head');


$filters = array_keys($this->filterForm->getGroup('filter'));
?>
<?php echo LayoutHelper::render('components.com_prototype.map.balloon'); ?>
<div id="prototype" class="map">
	<div id="itemList" data-prototype-map-itemlist="container">
		<div data-prototype-map-itemlist="items"></div>
	</div>
	<div id="map" data-prototype-map>
		<form action="<?php echo htmlspecialchars(Factory::getURI()->toString()); ?>" method="get" name="adminForm"
			  data-prototype-map-filter data-afterInit="show">
			<?php foreach ($filters as $filter): ?>
				<?php echo $this->filterForm->renderField(str_replace('filter_', '', $filter), 'filter'); ?>
			<?php endforeach; ?>

			<button type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<a href="<?php echo $this->category->link; ?>"><?php echo Text::_('JCLEAR'); ?></a>
		</form>
		<div id="zoom" data-afterInit="show">
			<a data-prototype-map-zoom="plus">+</a>
			<span data-prototype-map-zoom="current"></span>
			<a data-prototype-map-zoom="minus">-</a>
		</div>
		<div id="counter" data-afterInit="show">
			<span data-prototype-map-counter="current">0</span>
			/
			<span data-prototype-map-counter="total">0</span>
		</div>
		<a id="geo" data-prototype-map-geo data-afterInit="show">
			G
		</a>
	</div>
</div>