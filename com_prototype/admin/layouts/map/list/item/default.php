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

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var  \Joomla\Registry\Registry $item Item data
 */
?>
<div data-prototype-item="<?php echo $item->get('id'); ?>">
	<h3>
		<a data-prototype-map-show-balloon="<?php echo $item->get('id'); ?>">
			<?php echo $item->get('title'); ?>
		</a>
	</h3>
</div>
<hr data-prototype-item="<?php echo $item->get('id'); ?>">
