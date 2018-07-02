<?php
/**
 * @package    Prototype Component
 * @version    1.0.4
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;

JLoader::register('PrototypeModelItem', JPATH_ADMINISTRATOR . '/components/com_prototype/models/item.php');

class PrototypeModelForm extends PrototypeModelItem
{

	/**
	 * Category children data
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	protected $_children = array();

	/**
	 * Current Category data
	 *
	 * @var    object
	 *
	 * @since  1.0.0
	 */
	protected $_category = array();

	/**
	 * Category parent data
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	protected $_parent = array();


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id', 0);
		$this->setState('item.id', $pk);

		$catid = $app->input->getInt('catid', 1);
		$this->setState('category.id', $catid);

		$return_view = $app->input->get('return_view', '');
		$this->setState('return_view', $return_view);

		// Load the parameters. Merge Global and Menu Item params into new object
		$params     = $app->getParams();
		$menuParams = new Registry;
		$menu       = $app->getMenu()->getActive();
		if ($menu)
		{
			$menuParams->loadString($menu->getParams());
		}
		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->setState('params', $mergedParams);

		$user = Factory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_prototype.item'))
			&& (!$user->authorise('core.edit', 'com_prototype.item')))
		{
			$this->setState('filter.published', 1);
		}
		else
		{
			$this->setState('filter.published', array(0, 1));
		}

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		parent::populateState();
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Joomla\CMS\Form\Form |boolean  A JForm object on success, false on failure
	 *
	 * @since  1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);
		$form->setValue('catid', '', $this->getState('category.id', 1));

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since  1.0.0
	 */
	protected function loadFormData()
	{
		$data = parent::loadFormData();

		if (is_object($data))
		{
			if (empty($data->id) && empty($data->created_by))
			{
				$data->created_by = Factory::getUser()->id;
			}

			$data->catid = $this->getState('category.id');
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.0.0
	 */
	public function save($data)
	{
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = ($pk == 0);

		$data['created'] = Factory::getDate()->toSql();

		if ($id = parent::save($data))
		{

			// Send Email
			$this->sendEmail($id, $isNew);

			Factory::getApplication()->input->set('id', $id);

			return $id;
		}

		return false;
	}

	/**
	 * Method to get type data for the current type
	 *
	 * @param   integer $pk    The id of the item.
	 *
	 * @param    bool   $isNew Is item vas new
	 *
	 *
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	protected function sendEmail($pk, $isNew)
	{
		$item            = $this->getItem($pk);
		$registry        = new Registry($item->images);
		$item->images    = $registry->toArray();
		$item->adminLink = Uri::root() . 'administrator/index.php?option=com_prototype&task=item.edit&id=' . $item->id;

		$category = $this->getCategory($item->catid);


		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_profiles/models', 'ProfilesModel');
		$profileModel       = BaseDatabaseModel::getInstance('Profile', 'ProfilesModel', array('ignore_request' => true));
		$author             = $profileModel->getItem($item->created_by);
		$author_information = $profileModel->getInformation($author);


		$language = Factory::getLanguage();
		$language->load('com_users', JPATH_ADMINISTRATOR, $language->getTag(), true);

		$layoutData = array(
			'item'     => $item,
			'category' => $category,
			'author'   => new Registry($author_information)
		);

		$subject = Text::_('COM_PROTOTYPE') . ': ';

		$subject .= ($isNew) ? Text::_('COM_PROTOTYPE_ITEM_SUBMIT_SAVE_SUCCESS') : Text::_('COM_PROTOTYPE_ITEM_SAVE_SUCCESS');
		$body    = LayoutHelper::render('components.com_prototype.mail.admin', $layoutData);

		$siteConfig      = Factory::getConfig();
		$componentConfig = ComponentHelper::getParams('com_prototype');

		$sender = array($siteConfig->get('mailfrom'), $siteConfig->get('sitename'));

		$recipient = explode(',', $componentConfig->get('admin_email', $siteConfig->get('mailfrom')));

		$mail = JFactory::getMailer();
		$mail->setSubject($subject);
		$mail->setSender($sender);
		$mail->addRecipient($recipient);
		$mail->setBody($body);
		$mail->isHtml(true);
		$mail->Encoding = 'base64';

		return $mail->send();
	}

	/**
	 * Method to get type data for the current type
	 *
	 * @param   integer $pk The id of the type.
	 *
	 * @return  mixed object|false
	 *
	 * @since  1.0.0
	 */
	public function getCategory($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

		if (!isset($this->_category[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('c.*')
					->from('#__prototype_categories AS c')
					->where('c.id = ' . (int) $pk);

				// Filter by published state.
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$query->where('c.state IN (' . implode(',', $published) . ')');
				}

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{
					return JError::raiseError(404, Text::_('COM_PROTOTYPE_ERROR_CATEGORY_NOT_FOUND'));
				}

				// Root
				$data->root = ($data->id == 1);

				// Links
				$returnView       = $this->getState('return_view');
				$data->listLink   = Route::_(PrototypeHelperRoute::getListRoute($data->id));
				$data->mapLink    = Route::_(PrototypeHelperRoute::getMapRoute($data->id));
				$data->cancelLink = ($returnView == 'map') ? $data->mapLink : $data->listLink;
				$data->formLink   = Route::_(PrototypeHelperRoute::getFormRoute($this->getState('item.id'),
					$data->id, $returnView));

				$registry     = new Registry($data->fields);
				$data->fields = $registry->toArray();

				$this->_category[$pk] = $data;
			}
			catch (Exception $e)
			{

				$this->setError($e);
				$this->_category[$pk] = false;

			}
		}

		return $this->_category[$pk];
	}

	/**
	 * Get the parent of this category
	 *
	 * @param   integer $pk     The id of the type.
	 * @param  integer  $parent The parent_id of the type.
	 *
	 * @return object
	 *
	 * @since  1.0.0
	 */
	public function &getParent($pk = null, $parent = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

		if (!isset($this->_parent[$pk]))
		{
			$db = Factory::getDbo();
			if (empty($parent))
			{
				$query = $db->getQuery(true)
					->select('parent_id')
					->from('#__prototype_categories')
					->where('id = ' . (int) $pk);
				$db->setQuery($query);
				$parent = $db->loadResult();
			}
			try
			{
				if ($parent > 1)
				{
					$query = $db->getQuery(true)
						->select(array('id', 'title', 'alias', 'parent_id', 'front_created', 'icon'))
						->from('#__prototype_categories')
						->where('id = ' . (int) $parent);

					$db->setQuery($query);
					$item = $db->loadObject();

					if ($item)
					{
						$item->listLink = Route::_(PrototypeHelperRoute::getListRoute($item->id));
						$item->mapLink  = Route::_(PrototypeHelperRoute::getMapRoute($item->id));
						$item->formLink = Route::_(PrototypeHelperRoute::getFormRoute($this->getState('item.id'),
							$item->id, $this->getState('return_view')));

						$this->_parent[$pk] = $item;
					}
					else
					{
						$this->_parent[$pk] = false;
					}
				}
				elseif ($parent == 1)
				{
					$root            = new stdClass();
					$root->id        = 1;
					$root->alias     = 'root';
					$root->title     = Text::_('COM_PROTOTYPE_CATEGORY_ROOT');
					$root->parent_id = 0;

					$this->_parent[$pk] = $root;
				}
				else
				{
					$this->_parent[$pk] = false;
				}

			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_parent[$pk] = false;
			}
		}

		return $this->_parent[$pk];
	}

	/**
	 * Get the Children of this category
	 *
	 * @param   integer $pk The id of the type.
	 *
	 * @return object
	 *
	 * @since  1.0.0
	 */
	public function &getChildren($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');
		if (!isset($this->_children[$pk]))
		{
			try
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select(array('id', 'title', 'alias', 'front_created', 'icon'))
					->from('#__prototype_categories')
					->where('parent_id = ' . (int) $pk)
					->where('front_created > 0')
					->order('lft ASC');

				// Filter by published state.
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$query->where('state IN (' . implode(',', $published) . ')');
				}

				$user = Factory::getUser();
				// Filter by access level.
				if (!$user->authorise('core.admin'))
				{
					$groups = implode(',', $user->getAuthorisedViewLevels());
					$query->where('access IN (' . $groups . ')');
				}

				$db->setQuery($query);
				$items = $db->loadObjectList();

				foreach ($items as &$item)
				{
					$item->listLink = Route::_(PrototypeHelperRoute::getListRoute($item->id));
					$item->mapLink  = Route::_(PrototypeHelperRoute::getMapRoute($item->id));
					$item->formLink = Route::_(PrototypeHelperRoute::getFormRoute($this->getState('item.id'),
						$item->id, $this->getState('return_view')));

				}

				$this->_children[$pk] = $items;

			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_children[$pk] = false;
			}
		}

		return $this->_children[$pk];
	}
}