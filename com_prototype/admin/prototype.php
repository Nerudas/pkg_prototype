<?php
/**
 * @package    Prototype Component
 * @version    1.3.5
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::register('PrototypeHelper', __DIR__ . '/helpers/prototype.php');
HTMLHelper::_('behavior.tabstate');

if (!Factory::getUser()->authorise('core.manage', 'com_prototype'))
{
	throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

$controller = BaseController::getInstance('Prototype');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();