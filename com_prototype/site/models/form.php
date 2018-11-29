<?php
/**
 * @package    Prototype Component
 * @version    1.3.6
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
JLoader::register('FieldTypesFilesHelper', JPATH_PLUGINS . '/fieldtypes/files/helper.php');

class PrototypeModelForm extends PrototypeModelItem
{

	/**
	 * Author data
	 *
	 * @var    object
	 *
	 * @since  1.0.0
	 */
	protected $_author = array();

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

				$registry      = new Registry(ComponentHelper::getParams('com_prototype')->get('presets', array()));
				$configs       = $registry->toArray();
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

				$presets = array();


				if (!empty($data->presets))
				{
					$registry = new Registry($data->presets);
					$rows     = $registry->toArray();

					foreach ($rows as $preset)
					{
						$preset['price_title']    = (!empty($configPresets['price'][$preset['price']])) ?
							$configPresets['price'][$preset['price']]->get('title', $preset['price']) : $preset['price'];
						$preset['delivery_title'] = (!empty($configPresets['delivery'][$preset['delivery']])) ?
							$configPresets['delivery'][$preset['delivery']]->get('title', $preset['delivery']) : $preset['delivery'];
						$preset['object_title']   = (!empty($configPresets['object'][$preset['object']])) ?
							$configPresets['object'][$preset['object']]->get('title', $preset['object']) : $preset['object'];
						$presets[$preset['key']]  = $preset;
					}
				}

				$data->presets = $presets;

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
						->select(array('id', 'title', 'alias', 'parent_id', 'front_created'))
						->from('#__prototype_categories')
						->where('id = ' . (int) $parent);

					$db->setQuery($query);
					$item = $db->loadObject();

					if ($item)
					{
						$imagesHelper = new FieldTypesFilesHelper();
						$item->icon   = $imagesHelper->getImage('icon', 'images/prototype/categories/' . $item->id, false, false);

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
					->select(array('id', 'title', 'alias', 'front_created'))
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

				$imagesHelper = new FieldTypesFilesHelper();

				foreach ($items as &$item)
				{
					$item->icon     = $imagesHelper->getImage('icon', 'images/prototype/categories/' . $item->id, false, false);
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

	/**
	 * Method to get Author data
	 *
	 * @param   integer $pk The id of the author.
	 *
	 * @return  mixed object|false
	 *
	 * @since  1.0.0
	 */
	public function getAuthor($pk = null)
	{
		if (empty($pk))
		{
			$item = $this->getItem();
			$pk   = (!empty($item->id)) ? $item->created_by : Factory::getUser()->id;
		}

		if (!isset($this->_author[$pk]))
		{
			try
			{
				$db           = $this->getDbo();
				$offline      = (int) ComponentHelper::getParams('com_profiles')->get('offline_time', 5) * 60;
				$offline_time = Factory::getDate()->toUnix() - $offline;
				$siteContacts = new Registry();
				$siteContacts->set('phones', ComponentHelper::getParams('com_prototype')->get('site_phones', array()));
				$imagesHelper = new FieldTypesFilesHelper();

				$query = $db->getQuery(true)
					->select(array(
						'author.id as author_id',
						'author.name as author_name',
						'author.status as author_status',
						'author.contacts as author_contacts',
						'(session.time IS NOT NULL) AS author_online',
						'(company.id IS NOT NULL) AS author_job',
						'company.id as author_job_id',
						'company.name as author_job_name',
						'company.contacts as author_job_contacts',
						'company.about as author_job_about',
						'employees.position as  author_position',
						'(employees.as_company > 0 AND company.id IS NOT NULL) as author_company',))
					->from($db->quoteName('#__profiles', 'author'))
					->where('author.id = ' . (int) $pk)
					->join('LEFT', '#__session AS session ON session.userid = author.id AND session.time > ' . $offline_time)
					->join('LEFT', '#__companies_employees AS employees ON employees.user_id = author.id AND ' .
						$db->quoteName('employees.key') . ' = ' . $db->quote(''))
					->join('LEFT', '#__companies AS company ON company.id = employees.company_id AND company.state = 1');

				$db->setQuery($query);

				$data = $db->loadObject();
				if (empty($data))
				{
					$this->_author[$pk] = false;

					return false;
				}

				// Set author
				$author         = new stdClass();
				$author->type   = ($data->author_company) ? 'legal' : 'natural';
				$author->online = $data->author_online;
				$author->status = $data->author_status;
				if (!$data->author_company)
				{
					$author->id        = $data->author_id;
					$author->name      = $data->author_name;
					$author->signature = (!empty($data->author_job_name)) ? $data->author_job_name : '';
					$author->avatar    = $imagesHelper->getImage('avatar', 'images/profiles/' . $data->author_id,
						'media/com_profiles/images/no-avatar.jpg', false);
					$author->link      = Route::_(ProfilesHelperRoute::getProfileRoute($data->author_id));
					$author->contacts  = new Registry($data->author_contacts);
				}
				else
				{
					$author->id        = $data->author_job_id;
					$author->name      = $data->author_job_name;
					$author->signature = (!empty($data->author_position)) ? $data->author_position : $data->author_name;
					$author->avatar    = $imagesHelper->getImage('logo', 'images/companies/' . $data->author_job_id, false, false);
					$author->link      = Route::_(CompaniesHelperRoute::getCompanyRoute($data->author_job_id));
					$author->contacts  = new Registry($data->author_job_contacts);
				}
				$author->contacts     = $author->contacts->toArray();
				$author->siteContacts = $siteContacts->toArray();

				$this->_author[$pk] = new Registry($author);
			}
			catch (Exception $e)
			{

				$this->setError($e);
				$this->_author[$pk] = false;

			}
		}

		return $this->_author[$pk];
	}

}