<?php
/**
 * @package    Prototype Component
 * @version    1.0.7
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
 * @var   Registry $item      Item data
 * @var   Registry $extra     Item extra data
 * @var   Registry $category  Category data
 * @var   Registry $placemark Placemark data
 */

//echo '<pre>', print_r($item, true), '</pre>';
//echo '<pre>', print_r($extra, true), '</pre>';
//echo '<pre>', print_r($category, true), '</pre>';
//echo '<pre>', print_r($placemark, true), '</pre>';
?>

<div class="item" data-prototype-item="<?php echo $item->get('id'); ?>">
	<h2><a data-prototype-show="<?php echo $item->get('id'); ?>"><?php echo $item->get('title'); ?></a></h2>
</div>
<hr data-prototype-item="<?php echo $item->get('id'); ?>">
