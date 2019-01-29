<?php
/**
 * @package    Prototype Component
 * @version    1.4.3
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Registry\Registry;

class PrototypeControllerImport extends BaseController
{

	protected $limit = 50;

	public function prepareFile()
	{
		$files = Factory::getApplication()->input->files->get('files', array(), 'array');
		if (!$file = array_shift($files))
		{
			return $this->setResponse(null, 'File not select', true);
		}

		$tmp        = Factory::getConfig()->get('tmp_path');
		$folder     = 'prototype_import_' . time();
		$folderPath = $tmp . '/' . $folder;
		$filePath   = $folderPath . '/' . 'import.csv';
		if (!Folder::create($folderPath))
		{
			return $this->setResponse(null, 'Create tmp folder', true);
		}

		if (!File::upload($file['tmp_name'], $filePath, false, true))
		{
			return $this->setResponse(null, 'Upload file', true);
		}

		$lines = array();
		$row   = 1;
		if (($handle = fopen($filePath, "r")) !== false)
		{
			while (($data = fgetcsv($handle, 1000, ",")) !== false)
			{
				$num     = count($data);
				$columns = array();
				$row++;
				for ($c = 0; $c < $num; $c++)
				{
					$columns[] = $data[$c];
				}
				$lines[] = $columns;
			}
			fclose($handle);
		}

		if (empty($lines))
		{
			return $this->setResponse(null, 'Empty csv lines', true);
		}

		$keys    = array_shift($lines);
		$jsons   = array();
		$dropper = 0;
		$counter = 0;
		$total   = 0;
		foreach ($lines as $column)
		{
			if ($counter == $this->limit)
			{
				$dropper = $dropper + $this->limit;
				$counter = 0;
			}
			if (!isset($jsons[$dropper]))
			{
				$jsons[$dropper] = array();
			}

			$values = array();
			foreach ($column as $v => $value)
			{
				$key          = $keys[$v];
				$values[$key] = $value;
			}

			$jsons[$dropper][] = $values;

			$counter++;
			$total++;
		}

		if (empty($jsons))
		{
			return $this->setResponse(null, 'Empty import objects', true);
		}

		foreach ($jsons as $o => $json)
		{
			$registry = new Registry($json);
			File::append($folderPath . '/import_' . $o . '.json', $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE)));
		}

		$response         = new stdClass();
		$response->total  = $total;
		$response->folder = $folder;
		$response->limit  = $this->limit;
		$response->offset = 0;

		return $this->setResponse($response, 'success', false);
	}

	public function importItems()
	{
		$folder = $this->input->get('folder', '');
		$offset = $this->input->get('offset', 0);
		$limit  = $this->input->get('limit', $this->limit);
		$total  = $this->input->get('total', 0);

		if (empty($folder))
		{
			return $this->setResponse(null, 'Empty folder param', true);
		}

		if ($offset >= $total)
		{
			return $this->setResponse(null, 'Finish', true);
		}

		$tmp        = Factory::getConfig()->get('tmp_path');
		$folderPath = $tmp . '/' . $folder;
		if (!Folder::exists($folderPath))
		{
			return $this->setResponse(null, 'Folder not fond', true);
		}

		$file = $folderPath . '/import_' . $offset . '.json';
		if (!File::exists($file) || !$context = File::read($file))
		{
			return $this->setResponse(null, 'Empty file', true);
		}

		$registry = new Registry($context);
		foreach ($registry->toArray() as $item)
		{
			$date   = Factory::getDate($item['date'])->toSql();
			$preset = $item['preset_price'] . '|' . $item['preset_delivery'] . '|' . $item['preset_object'];

			$map = array
			(
				'placemark' => array
				(
					'coordinates' => '["' . $item['latitude'] . '", "' . $item['longitude'] . '"]',
					'latitude'    => $item['latitude'],
					'longitude'   => $item['longitude'],
				),

				'params' => array
				(
					'center'    => '["' . $item['latitude'] . '", "' . $item['longitude'] . '"]',
					'latitude'  => $item['latitude'],
					'longitude' => $item['longitude'],
					'zoom'      => 10,
				)

			);

			$data  = array(
				'created_by'      => $item['created_by'],
				'title'           => $item['title'],
				'catid'           => $item['catid'],
				'preset'          => $preset,
				'preset_price'    => $item['preset_price'],
				'preset_delivery' => $item['preset_delivery'],
				'preset_object'   => $item['preset_object'],
				'price'           => $item['price'],
				'text'            => $item['text'],
				'location'        => $item['location'],
				'external_link'   => $item['external_link'],
				'map'             => $map,
				'state'           => 0,
				'region'          => $item['region'],
				'access'          => 1,
				'active'          => 1,
				'date'            => $date,
				'created'         => $date,
				'publish_up'      => $date,
			);
			$model = $this->getModel();
			$model->save($data);
		}

		$response         = new stdClass();
		$response->total  = $total;
		$response->folder = $folder;
		$response->limit  = $limit;
		$response->offset = $offset + $limit;

		return $this->setResponse($response, 'success', false);
	}

	public function clear()
	{
		$folder = $this->input->get('folder', '');

		if (empty($folder))
		{
			return $this->setResponse(null, 'Empty folder param', true);
		}

		$tmp        = Factory::getConfig()->get('tmp_path');
		$folderPath = $tmp . '/' . $folder;
		if (!Folder::exists($folderPath) || Folder::delete($folderPath))
		{
			return $this->setResponse(null, 'Success');
		}
		else
		{
			return $this->setResponse(null, 'False', true);
		}

	}

	protected function setResponse($response = null, $message = null, $error = false)
	{
		echo new JsonResponse($response, $message, $error);
		Factory::getApplication()->close();

		return (!$error);
	}

	/**
	 *
	 * Proxy for getModel.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config The array of possible config values. Optional.
	 *
	 * @return  JModelLegacy
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Item', $prefix = 'PrototypeModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}