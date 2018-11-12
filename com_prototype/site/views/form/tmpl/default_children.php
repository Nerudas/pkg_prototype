<?php
/**
 * @package    Prototype Component
 * @version    1.3.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

foreach ($this->children as $child)
{
	echo '<a href="' . $child->formLink . '">' . $child->title . '</a><br />';
}