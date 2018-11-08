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

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Registry $author Author data
 */

echo '<pre>', print_r('author.default', true), '</pre>';
echo '<pre>', print_r($author, true), '</pre>';