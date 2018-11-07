<?php
/**
 * @package    Prototype Component
 * @version    1.3.0
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
use Joomla\CMS\Uri\Uri;

class PrototypeControllerCategory extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_PROTOTYPE_CATEGORY';

	/**
	 * Method to get Item placemark
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function getPlacemark()
	{
		$data   = $this->input->post->get('jform', array(), 'array');
		$preset = (!empty($data['preset_demo'])) ? $data['presets'][$data['preset_demo']] : array_shift($data['presets']);

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
				$configPresets[$key][$conf['value']] = new Registry($conf);
			}
		}

		$presetPrice = (!empty($configPresets['price'][$preset['price']])) ? $configPresets['price'][$preset['price']] : false;

		$placemark = new Registry();
		$placemark->set('id', $data['id']);
		$placemark->set('title', $preset['title']);
		$placemark->set('price', '----');
		$placemark->set('preset_price', $presetPrice);
		$placemark->set('preset_icon', $preset['icon']);
		$placemark->set('show_price', ($preset['price'] != 'none'));

		$layout = new FileLayout('components.com_prototype.map.placemark');
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
}