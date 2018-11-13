<?php
/**
 * @package    Prototype Component
 * @version    1.3.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Registry\Registry;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Component\ComponentHelper;

class PrototypeControllerItem extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_PROTOTYPE_ITEM';

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string $url  URL to redirect to.
	 * @param   string $msg  Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string $type Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return  \JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.0.0
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		if ($this->input->get('task') == 'reload')
		{
			sleep(1);
		}

		return parent::setRedirect($url, $msg, $type);
	}

	/**
	 * Method to get Item placemark
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function getPlacemark()
	{
		$data = $this->input->post->get('jform', array(), 'array');

		if (empty($data['catid']) || empty($data['preset']))
		{
			return $this->returnDefaultPlacemark();
		}

		$category = $this->getModel('Category')->getItem($data['catid']);
		if (empty($category) || empty($category->presets))
		{
			return $this->returnDefaultPlacemark();
		}

		$registry = new Registry(ComponentHelper::getParams('com_prototype')->get('presets', array()));
		$configs  = $registry->toArray();

		$configPresets = array();
		foreach ($configs as $key => $config)
		{
			if (!isset($configPresets[$key]))
			{
				$configPresets[$key] = array();
			}
			foreach ($config as $conf)
			{
				$configPresets[$key][$conf['value']] = (object) $conf;
			}
		}

		$presets = array();
		foreach ($category->presets as &$preset)
		{
			$presets[$preset['key']] = $preset;
		}

		if (empty($presets[$data['preset']]))
		{
			return $this->returnDefaultPlacemark();
		}
		$preset = $presets[$data['preset']];

		$presetPrice = (!empty($configPresets['price'][$preset['price']])) ? $configPresets['price'][$preset['price']] : false;
		$presetIcon  = (!empty($preset['icon'])) ? $preset['icon'] : '';

		$placemark = new Registry();
		$placemark->set('id', $data['id']);
		$placemark->set('title', $data['title']);
		$placemark->set('price', $data['price']);
		$placemark->set('preset_price', ($presetPrice)? $presetPrice->title : '');
		$placemark->set('preset_icon', $presetIcon);
		$placemark->set('show_price', (!empty($data['price'])));

		$layout = new FileLayout('components.com_prototype.map.placemark.default');
		$html   = $layout->render(array('placemark' => $placemark));
		preg_match('/data-placemark-coordinates="([^"]*)"/', $html, $matches);
		$coordinates = '[]';
		if (!empty($matches[1]))
		{
			$coordinates = $matches[1];
			$html        = str_replace($matches[0], '', $html);
		}

		$options                 = array();
		$options['customLayout'] = $html;

		$iconShape              = new stdClass();
		$iconShape->type        = 'Polygon';
		$iconShape->coordinates = json_decode($coordinates);
		$options['iconShape']   = $iconShape;

		echo new JsonResponse($options);
		Factory::getApplication()->close();

		return true;
	}

	/**
	 * Method to return default placemark
	 *
	 * @return bool
	 *
	 * @since 1.3.0
	 */
	protected function returnDefaultPlacemark()
	{
		$data      = $this->input->post->get('jform', array(), 'array');
		$placemark = new Registry();
		$placemark->set('id', $data['id']);
		$placemark->set('title', $data['title']);
		$placemark->set('price', $data['price']);
		$placemark->set('show_price', (!empty($data['price'])));

		$layout = new FileLayout('components.com_prototype.map.placemark.default');
		$html   = $layout->render(array('placemark' => $placemark));
		preg_match('/data-placemark-coordinates="([^"]*)"/', $html, $matches);
		$coordinates = '[]';
		if (!empty($matches[1]))
		{
			$coordinates = $matches[1];
			$html        = str_replace($matches[0], '', $html);
		}

		$options                 = array();
		$options['customLayout'] = $html;

		$iconShape              = new stdClass();
		$iconShape->type        = 'Polygon';
		$iconShape->coordinates = json_decode($coordinates);
		$options['iconShape']   = $iconShape;

		echo new JsonResponse($options);
		Factory::getApplication()->close();

		return false;
	}

	/**
	 *  Method to prolong items to 3 days.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function prolong_3d()
	{
		$this->prolong(array(3, 'day'));
	}

	/**
	 * Method to prolong items to 1 week.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function prolong_1w()
	{
		$this->prolong(array(1, 'week'));
	}

	/**
	 * Method to prolong items to 1 month.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function prolong_1m()
	{
		$this->prolong(array(1, 'month'));
	}

	/**
	 * Method to prolong items.
	 *
	 * @param array $plus date plus publish_down
	 *
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function prolong($plus = array())
	{
		$data = $this->input->post->get('jform', array(), 'array');

		if (!empty($plus))
		{
			$data['plus_payment_down'] = array(
				'number'   => $plus[0],
				'variable' => $plus[1],
			);
		}

		$this->input->post->set('jform', $data);
		$this->task = 'apply';

		return parent::save();
	}
}