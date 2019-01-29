<?php
/**
 * @package    Prototype Component
 * @version    1.4.3
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
 * @var   Registry $item   Item data
 * @var   Registry $author Author data
 */
?>
<div class="item" data-prototype-item="<?php echo $item->get('id'); ?>">
	<h2><a data-prototype-list-show-balloon="<?php echo $item->get('id'); ?>"><?php echo $item->get('title'); ?></a></h2>
	<div>
		<a data-prototype-list-show-author="<?php echo $item->get('id'); ?>"><?php echo $author->get('name'); ?></a>
	</div>
</div>
<hr data-prototype-item="<?php echo $item->get('id'); ?>">
