<?php
/**
 * @package    Prototype Component
 * @version    1.0.4
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Registry $item         Item data
 * @var   Registry $extra        Item extra data
 * @var   Registry $category     Category data
 * @var   Registry $extra_filter Extra Filter data
 * @var   Registry $placemark    Placemark data
 */


$image = ($placemark->get('image', false)) ? $placemark->get('image') : 'media/com_prototype/images/placemark.png';

$publish_down = $item->get('publish_down', '0000-00-00 00:00:00');
if ($publish_down == '0000-00-00 00:00:00')
{
	$publish_down = false;
}
if ($publish_down)
{
	$publish_down = new Date($publish_down);
	$publish_down->toSql();
}

$onModeration = (!$item->get('state', 0) || ($publish_down && $publish_down < Factory::getDate()->toSql()));
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
		max-width: 32px;
		max-height: 32px
	}

	[data-prototype-placemark].default[data-viewed="true"] .title {
		left: 32px;
		line-height: 15px;
		font-size: 12px;
		padding: 0 3px
	}

	[data-prototype-placemark].default.onModeration .title {
		background-color: #da314b;
		color: #fff;
	}
</style>
<div data-prototype-placemark="<?php echo $item->get('id', 'x'); ?>"
	 data-placemark-coordinates="[[[-24, -48],[300, -48],[24, -8],[24, -8],[0, 0],[-24, -10],[-24, -10]]]"
	 class="placemark default<?php echo ($onModeration) ? ' onModeration' : ''; ?>" data-viewed="false">
	<?php echo HTMLHelper::image($image,
		$item->get('title', Text::_('JGLOBAL_TITLE'))); ?>
	<div class="title"><?php echo $item->get('title', Text::_('JGLOBAL_TITLE')); ?></div>
</div>
