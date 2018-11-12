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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('script', '//api-maps.yandex.ru/2.1/?lang=ru-RU', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'media/com_prototype/js/balloon.min.js', array('version' => 'auto'));

HTMLHelper::_('script', 'media/com_prototype/js/map.min.js', array('version' => 'auto'));
HTMLHelper::_('stylesheet', 'media/com_prototype/css/map.min.css', array('version' => 'auto'));
$doc = Factory::getDocument();
$doc->addScriptDeclaration("function showPrototypeMapBalloon() {jQuery(('[data-prototype-map-balloon]')).show();}");