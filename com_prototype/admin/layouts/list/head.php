<?php
/**
 * @package    Prototype Component
 * @version    1.3.3
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
HTMLHelper::_('script', 'media/com_prototype/js/list.min.js', array('version' => 'auto'));

$doc = Factory::getDocument();
$doc->addScriptDeclaration("function showPrototypeListBalloon() {jQuery(('[data-prototype-list-balloon]')).show();}");
$doc->addScriptDeclaration("function showPrototypeListAuthor() {jQuery(('[data-prototype-list-author]')).show();}");

$app       = Factory::getApplication();
$component = $app->input->get('option', '');
$view      = $app->input->get('view', '');
$item_id   = $app->input->get('item_id', 0);
if ($component == 'com_prototype' && $view == 'list' && !empty($item_id))
{
	$doc->addScriptDeclaration('	jQuery(document).ready(function () {
		jQuery(\'[data-prototype-list-show-balloon="' . $item_id . '"]\').trigger(\'click\');
	});');
}