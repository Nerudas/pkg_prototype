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

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Registry\Registry;
use Joomla\CMS\Layout\FileLayout;

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
	 * Method to update item icon
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function updateImages()
	{
		$app   = Factory::getApplication();
		$id    = $app->input->get('id', 0, 'int');
		$value = $app->input->get('value', '', 'raw');
		$field = $app->input->get('field', '', 'raw');
		if (!empty($id) & !empty($field))
		{
			JLoader::register('imageFolderHelper', JPATH_PLUGINS . '/fieldtypes/ajaximage/helpers/imagefolder.php');
			$helper = new imageFolderHelper('images/prototype/categories');
			$helper->saveImagesValue($id, '#__prototype_categories', $field, $value);
		}

		$app->close();

		return true;
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
		$app           = Factory::getApplication();
		$data          = $this->input->post->get('jform', array(), 'array');
		$data['image'] = (!empty($placemark->images) && !empty(reset($placemark->images)['src'])) ?
			reset($placemark->images)['src'] : false;

		$item     = new Registry($data);
		$category = new Registry($data);

		$placemark = array();
		if (!empty($data['placemark_id']))
		{
			$placemark = $this->getModel('Placemark')->getItem($data['placemark_id']);

			if ($placemark)
			{
				$registry          = new Registry($placemark->images);
				$placemark->images = $registry->toArray();
				$placemark->image = (!empty($placemark->images) && !empty(reset($placemark->images)['src'])) ?
					reset($placemark->images)['src'] : false;
			}
		}

		$placemark = new Registry($placemark);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('template')
			->from('#__template_styles')
			->where('client_id = 0')
			->order('home DESC ');
		$db->setQuery($query);
		$templates   = $db->loadColumn();
		$layoutPaths = array();
		foreach (array_unique($templates) as $template)
		{
			$layoutPaths[] = JPATH_ROOT . '/templates/' . $template . '/html/layouts';
		}
		$layoutPaths[] = JPATH_ROOT . '/layouts';

		$layoutName = $placemark->get('layout', 'default');
		if (!JPath::find($layoutPaths, 'components/com_prototype/placemarks/' . $layoutName . '.php'))
		{
			$layoutName = 'default';
		}

		$layoutID = 'components.com_prototype.placemarks.' . $layoutName;
		$layout   = new FileLayout($layoutID);
		$layout->setIncludePaths($layoutPaths);

		$displayData = array(
			'item'      => $item,
			'category'  => $category,
			'placemark' => $placemark
		);

		$html = $layout->render($displayData);
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
		$app->close();

		return true;
	}
}