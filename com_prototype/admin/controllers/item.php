<?php
/**
 * @package    Prototype Component
 * @version    1.0.6
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
			$helper = new imageFolderHelper('images/prototype/items');
			$helper->saveImagesValue($id, '#__prototype_items', $field, $value);
		}

		$app->close();

		return true;
	}

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
		$app           = Factory::getApplication();
		$data          = $this->input->post->get('jform', array(), 'array');
		$data['image'] = (!empty($data['images']) && !empty(reset($data['images'])['src'])) ?
			reset($data['images'])['src'] : false;

		$item  = new Registry($data);
		$extra = new Registry($data['extra']);

		$category = array();
		if (!empty($data['catid']))
		{
			$category = $this->getModel('Category')->getItem($data['catid']);
			if ($category && empty($data['placemark_id']))
			{
				$data['placemark_id'] = $category->placemark_id;
			}
		}
		$category     = new Registry($category);
		$extra_filter = new Registry(array());

		$placemark = array();
		if (!empty($data['placemark_id']))
		{
			$placemark = $this->getModel('Placemark')->getItem($data['placemark_id']);

			if ($placemark)
			{
				$registry          = new Registry($placemark->images);
				$placemark->images = $registry->toArray();
				$placemark->image  = (!empty($placemark->images) && !empty(reset($placemark->images)['src'])) ?
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
		$language = Factory::getLanguage();
		foreach (array_unique($templates) as $template)
		{
			$layoutPaths[] = JPATH_ROOT . '/templates/' . $template . '/html/layouts';
			$language->load('tpl_' . $template, JPATH_SITE, $language->getTag(), true);
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
			'item'         => $item,
			'extra'        => $extra,
			'category'     => $category,
			'extra_filter' => $extra_filter,
			'placemark'    => $placemark
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