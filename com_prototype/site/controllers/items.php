<?php
/**
 * @package    Prototype Component
 * @version    1.3.8
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;

class PrototypeControllerItems extends AdminController
{
	/**
	 * Method to items total
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function getTotal()
	{
		$model   = $this->getModel('Items');
		$total   = ($total = $model->getTotal()) ? $total : 0;
		$success = ($total || $total > 0);

		echo new JsonResponse($total, '', !$success);
		Factory::getApplication()->close();
	}

	/**
	 * Method to items palcemarks
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function getPlacemarks()
	{
		$success  = false;
		$response = '';
		$model    = $this->getModel();
		if ($items = $model->getItems())
		{
			$success              = true;
			$response             = new stdClass();
			$response->count      = count($items);
			$response->placemarks = array();
			$response->listItems  = array();
			foreach ($items as $id => $item)
			{
				if ($item->map)
				{
					$html = $item->render->mapPlacemark;
					preg_match('/data-placemark-coordinates="([^"]*)"/', $html, $matches);
					$coordinates = '[]';
					if (!empty($matches[1]))
					{
						$coordinates = $matches[1];
						$html        = str_replace($matches[0], '', $html);
					}

					preg_match('/data-placemark-coordinates-viewed="([^"]*)"/', $html, $matches);
					$coordinates_viewed = $coordinates;
					if (!empty($matches[1]))
					{
						$coordinates_viewed = $matches[1];
						$html               = str_replace($matches[0], '', $html);
					}

					$iconShape                     = new stdClass();
					$iconShape->type               = 'Polygon';
					$iconShape->coordinates        = json_decode($coordinates);
					$iconShape->coordinates_viewed = json_decode($coordinates_viewed);

					$placemark                          = $item->map->placemark;
					$placemark->id                      = $item->id;
					$placemark->options                 = array();
					$placemark->options['customLayout'] = $html;
					$placemark->options['iconShape']    = $iconShape;

					$response->placemarks[$id] = $placemark;
					$response->listItems[$id]  = $item->render->mapListItem;
				}
			}
		}

		echo new JsonResponse($response, '', !$success);
		Factory::getApplication()->close();
	}

	/**
	 * Method to item balloon
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function getBalloon()
	{
		$app      = Factory:: getApplication();
		$success  = false;
		$response = '';
		$item_id  = $app->input->get('item_id');
		$model    = $this->getModel();

		$model->setState('filter.item_id', $item_id);
		if ($item_id && $items = $model->getItems())
		{
			$item = (!empty($items[$item_id])) ? $items[$item_id] : false;
			if ($item)
			{
				$success        = true;
				$response       = new stdClass();
				$response->html = $item->render->balloon;

				$model->hit();
			}

		}
		echo new JsonResponse($response, '', !$success);
		Factory::getApplication()->close();
	}

	/**
	 * Method to item author
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function getAuthor()
	{
		$app      = Factory:: getApplication();
		$success  = false;
		$response = '';
		$item_id  = $app->input->get('item_id');
		$model    = $this->getModel();

		$model->setState('filter.item_id', $item_id);
		if ($item_id && $items = $model->getItems())
		{
			$item = (!empty($items[$item_id])) ? $items[$item_id] : false;
			if ($item)
			{
				$success        = true;
				$response       = new stdClass();
				$response->html = $item->render->author;

				$model->hit();
			}

		}
		echo new JsonResponse($response, '', !$success);
		Factory::getApplication()->close();
	}

	/**
	 *
	 * Proxy for getModel.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Items', $prefix = 'PrototypeModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}
}