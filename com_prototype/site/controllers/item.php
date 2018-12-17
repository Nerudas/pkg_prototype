<?php
/**
 * @package    Prototype Component
 * @version    1.4.0
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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

JLoader::register('FieldTypesFilesHelper', JPATH_PLUGINS . '/fieldtypes/files/helper.php');

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

		$imagesHelper = new FieldTypesFilesHelper();

		if ($itemPresetIcon = $imagesHelper->getImage('preset_icon', $data['images_folder'], false, false))
		{
			$presetIcon = $itemPresetIcon;
		}


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
	 * Method to save a record.
	 *
	 * @param   string $key    The name of the primary key of the URL variable.
	 * @param   string $urlVar The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function save($key = null, $urlVar = null)
	{
		$result      = parent::save($key, $urlVar);
		$app         = Factory::getApplication();
		$data        = $this->input->post->get('jform', array(), 'array');
		$id          = $app->input->getInt('id');
		$catid       = $app->input->getInt('catid');
		$return_view = $app->input->getCmd('return_view');

		if ($result)
		{
			$category  = $this->getModel()->getCategory($catid);
			$catParams = new Registry($category->attribs);

			echo '<pre>', print_r($category, true), '</pre>';

			$addText  = $catParams->get('save_text_add', $this->text_prefix . '_SUBMIT_SAVE_SUCCESS');
			$editText = $catParams->get('save_text_edit', $this->text_prefix . '_SAVE_SUCCESS');

			$this->setMessage(Text::_(($data['id'] == 0) ? $addText : $editText));
		}

		$errorLink   = PrototypeHelperRoute::getFormRoute($id, $catid, $return_view);
		$successLink = ($return_view != 'map') ? PrototypeHelperRoute::getListRoute($catid) . '&item_id=' . $id :
			PrototypeHelperRoute::getMapRoute($catid) .
			'&center=' . $data['map']['params']['latitude'] . ',' . $data['map']['params']['longitude'] .
			'&zoom=' . $data['map']['params']['zoom'] .
			'&item_id=' . $id;

		$this->setRedirect(Route::_(($result) ? $successLink : $errorLink));

		return $result;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Form', $prefix = 'PrototypeModel', $config = array('ignore_request' => true))
	{
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_prototype/models');

		return parent::getModel($name, $prefix, $config);
	}


	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user     = Factory::getUser();
		$selector = (!empty($data[$key])) ? $data[$key] : 0;
		$author   = (!empty($data['created_by'])) ? $data['created_by'] : 0;
		$canEdit  = $user->authorise('core.edit', 'com_prototype.item.' . $selector) ||
			($user->authorise('core.edit.own', 'com_prototype.item.' . $selector)
				&& $author == $user->id);

		return $canEdit;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array $data An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function allowAdd($data = array())
	{
		if (!parent::allowAdd($data) || !$this->getModel('Category')->getItem($data['catid'])->front_created)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string $key The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function cancel($key = null)
	{
		parent::cancel($key);

		$app         = Factory::getApplication();
		$catid       = $app->input->getInt('catid');
		$return_view = $app->input->getCmd('return_view');

		$return = ($return_view != 'map') ? PrototypeHelperRoute::getListRoute($catid) :
			PrototypeHelperRoute::getMapRoute($catid);

		$this->setRedirect(Route::_($return));

		return $result;
	}
}