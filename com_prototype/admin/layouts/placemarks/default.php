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

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

jimport('joomla.filesystem.file');

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Registry $item      Item data
 * @var   Registry $placemark Placemark data
 */

$image = ($placemark->get('image', false)) ? $placemark->get('image') : 'media/com_prototype/images/placemark.png';

?>
<style>
	[data-prototype-placemark].default {
		display: block;
		position: relative;
		width: 48px;
		height: 48px;
		margin-top: -48px;
		margin-left: -24px;
		color: inherit
	}

	[data-prototype-placemark].default img {
		max-width: 48px;
		max-height: 48px;
	}

	[data-prototype-placemark].default .title {
		position: absolute;
		top: 0;
		left: 48px;
		background: #fff;
		line-height: 30px;
		max-width: 150px;
		text-overflow: ellipsis;
		padding: 0 5px;
		border: 1px solid #e5e5e5;
		white-space: nowrap;
		overflow: hidden;
		font-size: 14px;
		box-sizing: border-box
	}

	[data-prototype-placemark].default[data-viewed="true"] {
		width: 32px;
		height: 32px;
		margin-top: -32px;
		margin-left: -16px;
		color: inherit;
		opacity: .75
	}

	[data-prototype-placemark].default[data-viewed="true"] img {
		width: 32px;
		height: 32px
	}

	[data-prototype-placemark].default[data-viewed="true"] .title {
		left: 32px;
		line-height: 15px;
		font-size: 12px;
		padding: 0 3px
	}
</style>
<div data-prototype-placemark="<?php echo $item->get('id', 'x'); ?>"
	 data-placemark-coordinates="[[[-24, -48],[300, -48],[24, -8],[24, -8],[0, 0],[-24, -10],[-24, -10]]]"
	 class="placemark default" data-viewed="false">
	<?php echo HTMLHelper::image($image,
		$item->get('title', Text::_('JGLOBAL_TITLE'))); ?>
	<div class="title"><?php echo $item->get('title', Text::_('JGLOBAL_TITLE')); ?></div>
</div>
