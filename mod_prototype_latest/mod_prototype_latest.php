<?php
/**
 * @package    Prototype - Latest Module
 * @version    1.0.8
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

// Include route helper
JLoader::register('PrototypeHelperRoute', JPATH_SITE . '/components/com_prototype/helpers/route.php');
JLoader::register('ProfilesHelperRoute', JPATH_SITE . '/components/com_profiles/helpers/route.php');
JLoader::register('CompaniesHelperRoute', JPATH_SITE . '/components/com_companies/helpers/route.php');

// Load Language
$language = Factory::getLanguage();
$language->load('com_prototype', JPATH_SITE, $language->getTag(), false);

// Initialize model
BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/components/com_prototype/models');
$model = BaseDatabaseModel::getInstance('Items', 'PrototypeModel', array('ignore_request' => true));
$model->setState('list.limit', $params->get('limit', 5));
$model->setState('filter.category', $params->get('category', 1));
if ((!Factory::getUser()->authorise('core.edit.state', 'com_prototype.item')) &&
	(!Factory::getUser()->authorise('core.edit', 'com_prototype.item')))
{
	$model->setState('filter.published', 1);
}
else
{
	$model->setState('filter.published', array(0, 1));
}
$model->setState('filter.for_when', $params->get('for_when', ''));
$model->setState('filter.allregions', $params->get('allregions', ''));
$model->setState('filter.onlymy', $params->get('onlymy', ''));
$model->setState('filter.author_id', $params->get('author_id', ''));
$model->setState('filter.company_id', $params->get('company_id', ''));

// Variables
$items    = $model->getItems();
$listLink = PrototypeHelperRoute::getListRoute($params->get('category', 1));
$mapLink  = PrototypeHelperRoute::getMapRoute($params->get('category', 1));

if (!empty($params->get('author_id', '')))
{
	$listLink .= '&filter[author_id]=' . $params->get('author_id');
	$mapLink  .= '&filter[author_id]=' . $params->get('author_id');
}
if (!empty($params->get('company_id', '')))
{
	$listLink .= '&filter[company_id]=' . $params->get('company_id');
	$mapLink  .= '&filter[company_id]=' . $params->get('company_id');
}
if (!empty($params->get('onlymy', '')))
{
	$listLink .= '&filter[onlymy]=' . $params->get('onlymy');
	$mapLink  .= '&filter[onlymy]=' . $params->get('onlymy');
}

$listLink   = Route::_($listLink);
$mapLink    = Route::_($mapLink);
$addLink    = Route::_(PrototypeHelperRoute::getFormRoute(null, $params->get('category', 1)));
$addMapLink = Route::_(PrototypeHelperRoute::getFormRoute(null, $params->get('category', 1), 'map'));

require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));