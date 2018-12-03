<?php
/**
 * @package    Prototype Component
 * @version    1.3.7
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
 * @var   Registry $item Item data
 */
echo '<pre>', print_r('balloon.default', true), '</pre>';
echo '<pre>', print_r($item, true), '</pre>';