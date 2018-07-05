<?php
/**
 * @package    Prototype Component
 * @version    1.0.7
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Registry\Registry;

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
			foreach ($items as $id => $item)
			{
				if ($item->placemark)
				{
					$response->placemarks[$id] = $item->placemark;
				}
			}

			// Get items view
			$name   = $this->input->get('view', 'map');
			$type   = Factory::getDocument()->getType();
			$path   = $this->basePath;
			$layout = $this->input->get('layout', 'default', 'string');
			$view   = $this->getView($name, $type, '', array('base_path' => $path, 'layout' => $layout));

			$view->setModel($this->getModel($name), true);
			$view->document     = Factory::getDocument();
			$view->items        = $items;
			$view->extra_filter = new Registry($model->getState('extra_filter'));
			$view->category     = $model->getCategory();

			$response->html = $view->loadTemplate('items');
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
			if ($item && $item->balloon)
			{
				$success             = true;
				$response            = new stdClass();
				$response->placemark = $item->placemark;
				$response->balloon   = $item->balloon;

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