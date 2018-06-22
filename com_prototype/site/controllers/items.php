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
		$app = Factory::getApplication();

		$model   = $this->getModel('Items');
		$total   = ($total = $model->getTotal()) ? $total : 0;
		$success = ($total || $total > 0);

		echo new JsonResponse($total, '', !$success);
		$app->close();
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
		$app = Factory::getApplication();
		$doc = Factory::getDocument();

		$success  = false;
		$response = '';
		if ($items = $this->getModel()->getItems())
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

		}

		// Get items view
		$name   = $this->input->get('view', 'map');
		$type   = $doc->getType();
		$path   = $this->basePath;
		$layout = $this->input->get('layout', 'default', 'string');
		$view   = $this->getView($name, $type, '', array('base_path' => $path, 'layout' => $layout));

		$view->setModel($this->getModel($name), true);
		$view->document = Factory::getDocument();
		$view->items    = $items;

		$response->html = $view->loadTemplate('items');

		echo new JsonResponse($response, '', !$success);
		$app->close();
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