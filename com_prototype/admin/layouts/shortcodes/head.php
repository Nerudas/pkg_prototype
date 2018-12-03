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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('script', 'media/com_prototype/js/shortcodes.min.js', array('version' => 'auto'));

$doc = Factory::getDocument();
$doc->addScriptDeclaration("function showPrototypeShortcodesBalloon() {jQuery(('[data-prototype-shortcodes-balloon]')).show();}");
$doc->addScriptDeclaration("function showPrototypeShortcodesAuthor() {jQuery(('[data-prototype-shortcodes-author]')).show();}");