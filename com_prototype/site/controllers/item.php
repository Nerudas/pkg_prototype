<?php
/**
 * @package    Prototype Component
 * @version    1.0.5
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