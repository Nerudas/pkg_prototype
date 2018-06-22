<?php
/**
 * @package    Prototype Component
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('script', '//api-maps.yandex.ru/2.1/?lang=ru-RU', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'media/com_prototype/js/balloon.min.js', array('version' => 'auto'));
HTMLHelper::_('script', 'media/com_prototype/js/map.min.js', array('version' => 'auto'));
HTMLHelper::_('stylesheet', 'media/com_prototype/css/map.min.css', array('version' => 'auto'));

$filters = array_keys($this->filterForm->getGroup('filter'));
?>
<script>
	function showPrototypeMapBalloon() {
		jQuery(('[data-prototype-balloon]')).show();
	}
</script>
<div data-prototype-balloon>
	<div data-prototype-balloon-error><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
	<div data-prototype-balloon-loading>...</div>
	<div data-prototype-balloon-content>
	</div>
</div>
<div id="prototype" class="map">
	<div id="itemList" data-prototype-itemlist="container">
		<div data-prototype-itemlist="items"></div>
	</div>
	<div id="map" data-prototype-map>
		<form action="<?php echo htmlspecialchars(Factory::getURI()->toString()); ?>" method="get" name="adminForm"
			  data-prototype-filter data-afterInit="show">
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
			<span data-prototype-counter="current">0</span>
			/
			<span data-prototype-counter="total">0</span>
		</div>
		<a id="geo" data-prototype-map-geo data-afterInit="show">
			G
		</a>
	</div>
</div>