<?php
/**
 * @package    Prototype Component
 * @version    1.3.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Registry $placemark Placemark data
 */


?>

<style>
	[data-prototype-placemark] {
		display: block;
		position: relative;
		width: 120px;
		height: 60px;
		margin-top: -60px;
		margin-left: -60px;
		color: inherit
	}

	[data-prototype-placemark] .title {
		position: absolute;
		bottom: 0;
		left: 0;
		width: 120px;
		height: 20px;
		padding: 1px 5px 3px;
		line-height: 1;
		font-size: 15px;
		text-align: center;
		background: #fff;
		border: 1px solid #dcdcdc;
		white-space: nowrap;
		overflow: hidden;
		box-sizing: border-box;
		z-index: 1;
	}

	[data-prototype-placemark] .title::after {
		position: absolute;
		right: 0;
		bottom: 0;
		content: '';
		width: 10px;
		height: 20px;
		background: rgba(255, 255, 255, 0.7);
	}

	[data-prototype-placemark] img {
		position: absolute;
		bottom: 20px;
		left: 0;
		max-width: 65px;
		max-height: 40px;
		z-index: 2;
	}

	[data-prototype-placemark] .price {
		position: absolute;
		top: 0;
		right: 0;
		width: 63px;
		height: 40px;
		padding: 0 5px;
		text-overflow: ellipsis;
		background: #fff;
		border: 1px solid #dcdcdc;
		border-bottom: none;
		white-space: nowrap;
		overflow: hidden;
		box-sizing: border-box;
		z-index: 1;
		vertical-align: bottom;
		text-align: right;
		line-height: 1;
	}

	[data-prototype-placemark] .price .type {
		font-size: 12px;
	}

	[data-prototype-placemark] .price .number {
		font-size: 20px;
		font-weight: bold;
		text-align: left;
	}

	[data-prototype-placemark][data-viewed="true"] {
		color: inherit;
		width: 90px;
		height: 45px;
		margin-top: -45px;
		margin-left: -45px;
		opacity: .75;
		filter: brightness(.9)
	}

	[data-prototype-placemark][data-viewed="true"] .title {
		width: 90px;
		height: 15px;
		font-size: 11px;
	}

	[data-prototype-placemark][data-viewed="true"] .title::after {
		content: '';
		width: 7px;
		height: 15px;
	}

	[data-prototype-placemark][data-viewed="true"] img {
		bottom: 15px;
		max-width: 49px;
		max-height: 30px;
	}

	[data-prototype-placemark][data-viewed="true"] .price {
		width: 51px;
		height: 30px;
		padding: 0 3px;
	}

	[data-prototype-placemark][data-viewed="true"] .price .type {
		font-size: 9px;
	}

	[data-prototype-placemark][data-viewed="true"] .price .number {
		font-size: 15px;
	}

	[data-prototype-placemark].onModeration .price {
		background-color: #da314b;
		color: #fff;
	}
</style>
<div data-prototype-placemark="<?php echo $placemark->get('id', 'x'); ?>"
	 data-placemark-coordinates="[[[-60, -60],[60, -60],[60, 0],[60, 0],[0, 0],[-60, -10],[-60, -10]]]"
	 data-placemark-coordinates-viewed="[[[-45, -45],[45, -45],[45, 0],[45, 0],[0, 0],[-45, -10],[-45, -10]]]"
	 class="placemark" data-viewed="false">
	<?php if ($placemark->get('show_price')): ?>
		<div class="price">
			<?php ?>
			<div class="type"><?php echo $placemark->get('preset_price', ''); ?></div>
			<div class="number">
				<?php echo $placemark->get('price', '---'); ?>
			</div>

		</div>
	<?php endif; ?>
	<img src="/<?php echo $placemark->get('preset_icon', 'media/com_prototype/images/placemark.png'); ?>"
		 alt="<?php echo $placemark->get('title', Text::_('JGLOBAL_TITLE')); ?>">

	<div class="title"><?php echo $placemark->get('title', Text::_('JGLOBAL_TITLE')); ?></div>
</div>