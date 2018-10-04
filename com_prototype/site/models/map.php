<?php
/**
 * @package    Prototype Component
 * @version    1.2.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JLoader::register('PrototypeModelItems', JPATH_SITE . '/components/com_prototype/models/items.php');

class PrototypeModelMap extends PrototypeModelItems
{
	/**
	 * Map Params
	 *
	 * @var     array
	 *
	 * @since  1.0.0
	 */
	protected $_mapParams = null;

	/**
	 * Get the map Params
	 *
	 * @return mixed bool | array
	 *
	 * @since  1.0.0
	 */
	public function getMapParams()
	{
		if (!is_array($this->_mapParams))
		{
			$app    = Factory::getApplication();
			$params = array();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_location/models', 'LocationModel');
			$regionsModel = BaseDatabaseModel::getInstance('Regions', 'LocationModel', array('ignore_request' => false));
			$region       = $regionsModel->getVisitorRegion();

			$params['center']          = array($region->latitude, $region->longitude);
			$params['latitude']        = $region->latitude;
			$params['longitude']       = $region->longitude;
			$params['zoom']            = $region->zoom;
			$params['catid']           = $this->getState('category.id');
			$params['priority_center'] = false;

			if (!empty($app->input->get('center')) || $app->input->getInt('zoom'))
			{

				$center = (!empty($app->input->get('center'))) ?
					explode(',', $app->input->get('center', '', 'text')) : false;
				$zoom   = (!empty($app->input->getInt('zoom'))) ? $app->input->getInt('zoom') : false;

				$params['priority_center']           = array();
				$params['priority_center']['center'] = $center;
				$params['priority_center']['zoom']   = $zoom;
			}

			if (!empty($app->input->getInt('item_id')))
			{
				$params['item_id'] = $app->input->getInt('item_id');
			}

			$this->_mapParams = $params;
		}

		return $this->_mapParams;
	}

	/**
	 * Method to get visitors count
	 *
	 * @return int
	 *
	 * @since 1.0.1
	 */
	public function getVisitors()
	{
		$offline      = (int) ComponentHelper::getParams('com_profiles')->get('offline_time', 5) * 60;
		$offline_time = Factory::getDate()->toUnix() - $offline;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('Count(*)')
			->from('#__session')
			->where('time > ' . $offline_time)
			->where('client_id = 0');
		$db->setQuery($query);

		return $db->loadResult();
	}
}