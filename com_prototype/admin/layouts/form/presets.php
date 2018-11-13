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

use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'media/com_prototype/js/form-presets.min.js', array('version' => 'auto'));

?>
<ul data-prototype-form="presets" style="display: none;">
	<?php foreach ($presets as $preset): ?>
		<li>
			<a data-preset="<?php echo $preset['key']; ?>"
			   data-preset-title="<?php echo $preset['title']; ?>"
			   data-preset-price="<?php echo $preset['price']; ?>"
			   data-preset-price_title="<?php echo $preset['price_title']; ?>"
			   data-preset-delivery="<?php echo $preset['delivery']; ?>"
			   data-preset-delivery_title="<?php echo $preset['delivery_title']; ?>"
			   data-preset-object="<?php echo $preset['object']; ?>"
			   data-preset-object_title="<?php echo $preset['object_title']; ?>">
				<?php echo $preset['title']; ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
