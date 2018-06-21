<?php
/**
 * @package    Prototype Component
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

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

			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_nerudas/models');
			$regionModel = JModelLegacy::getInstance('regions', 'NerudasModel');
			$region      = $regionModel->getRegion($app->input->cookie->get('region', '*'));

			$params['center']    = array($region->latitude, $region->longitude);
			$params['latitude']  = $region->latitude;
			$params['longitude'] = $region->longitude;
			$params['zoom']      = $region->zoom;
			$params['catid']     = $this->getState('category.id');

			$this->_mapParams = $params;
		}

		return $this->_mapParams;
	}
}