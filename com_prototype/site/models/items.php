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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

jimport('joomla.filesystem.file');

JLoader::register('FieldTypesFilesHelper', JPATH_PLUGINS . '/fieldtypes/files/helper.php');

class PrototypeModelItems extends ListModel
{
	/**
	 * Name of the filter form to load
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $filterFormName = 'filter_items';
	/**
	 * Categories
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_categories = array();

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
	 * Category children data
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	protected $_children = array();

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @since  1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'i.id',
				'title', 'i.title',
				'text', 'i.text',
				'location', 'i.location',
				'price', 'i.price',
				'preset_price', 'i.preset_price',
				'preset_delivery', 'i.preset_delivery',
				'preset_object', 'i.preset_object',
				'external_link', 'i.external_link',
				'images', 'i.images',
				'state', 'i.state',
				'catid', 'i.catid',
				'created', 'i.created',
				'created_by', 'i.created_by',
				'payment_number', 'i.payment_number',
				'payment_down', 'i.payment_down',
				'map', 'i.map',
				'latitude', 'i.latitude',
				'longitude', 'i.longitude',
				'attribs', 'i.attribs',
				'access', 'i.access',
				'hits', 'i.hits',
				'region', 'i.region',
				'tags_search', 'i.tags_search',
				'tags_map', 'i.tags_map',
			);
		}
		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app  = Factory::getApplication('site');
		$user = Factory::getUser();

		// Set id state
		$pk = $app->input->getInt('id', 1);
		$this->setState('category.id', $pk);

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

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$category = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '');
		$this->setState('filter.category', $category);


		if ((!$user->authorise('core.edit.state', 'com_prototype.item'))
			&& (!$user->authorise('core.edit', 'com_prototype.item')))
		{
			$this->setState('filter.published', 1);
		}
		else
		{
			$this->setState('filter.published', array(0, 1));
		}

		$onlymy    = $this->getUserStateFromRequest($this->context . '.filter.onlymy', 'filter_onlymy', '');
		$author_id = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id', '');
		if (!empty($author_id) && $author_id == $user->id)
		{
			$onlymy = 1;
		}
		$this->setState('filter.onlymy', $onlymy);
		$this->setState('filter.author_id', $author_id);

		$company_id = $this->getUserStateFromRequest($this->context . '.filter.company_id', 'filter_company_id', '');
		$this->setState('filter.company_id', $company_id);

		$coordinates = $this->getUserStateFromRequest($this->context . '.filter.coordinates', 'filter_coordinates', '');
		$this->setState('filter.coordinates', $coordinates);

		// List state information.
		parent::populateState($ordering, $direction);

		// Set limit & limitstart for query.
		$this->setState('list.limit', $params->get('items_limit', 10, 'uint'));
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

		// Set ordering & direction for query.
		$ordering  = empty($ordering) ? 'i.created' : $ordering;
		$direction = empty($direction) ? 'desc' : $direction;
		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since  1.0.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('map');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.category');
		$id .= ':' . $this->getState('filter.onlymy');
		$id .= ':' . $this->getState('filter.author_id');
		$id .= ':' . $this->getState('filter.company_id');
		$id .= ':' . serialize($this->getState('filter.item_id'));
		$id .= ':' . $this->getState('filter.item_id.include');
		$id .= ':' . serialize($this->getState('filter.coordinates'));

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since  1.0.0
	 */
	protected function getListQuery()
	{
		$user  = Factory::getUser();
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('i.*', 'r.name AS region_name'))
			->from($db->quoteName('#__prototype_items', 'i'));

		// Join over the author.
		$offline      = (int) ComponentHelper::getParams('com_profiles')->get('offline_time', 5) * 60;
		$offline_time = Factory::getDate()->toUnix() - $offline;
		$query->select(array(
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
			'(employees.as_company > 0 AND company.id IS NOT NULL) as author_company',
		))
			->join('LEFT', '#__profiles AS author ON author.id = i.created_by')
			->join('LEFT', '#__session AS session ON session.userid = author.id AND session.time > ' . $offline_time)
			->join('LEFT', '#__companies_employees AS employees ON employees.user_id = author.id AND ' .
				$db->quoteName('employees.key') . ' = ' . $db->quote(''))
			->join('LEFT', '#__companies AS company ON company.id = employees.company_id AND company.state = 1');

		// Join over the categories.
		$query->join('LEFT', '#__prototype_categories AS c ON c.id = i.catid');

		// Join over the regions.
		$query->select(array('r.id as region_id', 'r.name as region_name'))
			->join('LEFT', '#__location_regions AS r ON r.id = i.region');


		// Filter by access level.
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('i.access IN (' . $groups . ')');
			$query->where('c.access IN (' . $groups . ')');
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');
		$onlymy   = $this->getState('filter.onlymy');
		if (empty($authorId) && !empty($onlymy) && !$user->guest)
		{
			$authorId = $user->id;
		}
		if (is_numeric($authorId))
		{
			$query->where('i.created_by = ' . (int) $authorId);
		}

		// Filter by company
		$companyId = $this->getState('filter.company_id');
		if (is_numeric($companyId))
		{
			$query->where('company.id = ' . (int) $companyId);
			$query->where('employees.as_company > 0');
		}


		// Filter by a single or group of items.
		$item_id = $this->getState('filter.item_id');
		if (is_numeric($item_id))
		{
			$type = $this->getState('filter.item_id.include', true) ? '= ' : '<> ';
			$query->where('i.id ' . $type . (int) $item_id);
		}
		elseif (is_array($item_id))
		{
			$item_id = ArrayHelper::toInteger($item_id);
			$item_id = implode(',', $item_id);
			$type    = $this->getState('filter.item_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('i.id ' . $type . ' (' . $item_id . ')');
		}

		// Filter by published state.
		$published = $this->getState('filter.published');
		if (!empty($published))
		{
			if (is_numeric($published))
			{
				$query->where('( i.state = ' . (int) $published .
					' OR ( i.created_by = ' . $user->id . ' AND i.state IN (0,1)))');
				$query->where('c.state = ' . (int) $published);
			}
			elseif (is_array($published))
			{
				$query->where('i.state IN (' . implode(',', $published) . ')');
				$query->where('c.state IN (' . implode(',', $published) . ')');
			}
		}
		// Filter by category
		$category = $this->getState('category.id');
		if ($category > 1)
		{
			$categoriesQuery = $db->getQuery(true)
				->select('sc.id')
				->from($db->quoteName('#__prototype_categories', 'sc'))
				->where($db->quoteName('c.alias') . ' <> ' . $db->quote('root'))
				->join('INNER', '#__prototype_categories as this ON sc.lft > this.lft AND sc.rgt < this.rgt')
				->where('(this.id = ' . (int) $category . ' OR sc.id = ' . $category . ')');

			// Add the category query
			$query->where('(i.catid IN (' . (string) $categoriesQuery . ') OR i.catid = ' . $category . ')');
		}

		// Filter by coordinates.
		$coordinates = $this->getState('filter.coordinates');
		if (!empty($coordinates))
		{
			$query->where('(i.latitude BETWEEN ' . $db->quote($coordinates['south']) . ' AND ' . $db->quote($coordinates['north']) . ')');
			if (isset($coordinates['west']) && isset($coordinates['east']))
			{
				if ($coordinates['west'] > 0 && $coordinates['east'] > 0 && $coordinates['west'] < $coordinates['east'])
				{
					$query->where('(i.longitude BETWEEN ' . $db->quote($coordinates['west']) .
						' AND ' . $db->quote($coordinates['east']) . ')');
				}
				if ($coordinates['west'] > 0 && $coordinates['east'] > 0 && $coordinates['west'] > $coordinates['east'])
				{
					$query->where('(i.longitude BETWEEN ' . $db->quote($coordinates['west']) . ' AND ' . $db->quote(180)
						. ' OR i.longitude BETWEEN ' . $db->quote(-180) . ' AND ' . $db->quote(0)
						. ' OR i.longitude BETWEEN ' . $db->quote(0) . ' AND ' . $db->quote($coordinates['east']) . ')');
				}
				if ($coordinates['west'] > 0 && $coordinates['east'] < 0 && $coordinates['west'] > $coordinates['east'])
				{
					$query->where('((i.longitude BETWEEN ' . $db->quote(-180) . ' AND ' . $db->quote($coordinates['east']) . ')' .
						' OR (i.longitude BETWEEN ' . $db->quote($coordinates['west']) . ' AND ' . $db->quote(180) . '))');
				}
				if ($coordinates['west'] < 0 && $coordinates['east'] < 0 && $coordinates['west'] < $coordinates['east'])
				{
					$query->where('(i.longitude BETWEEN ' . $db->quote($coordinates['west']) . ' AND ' . $db->quote($coordinates['east']) . ')');
				}
				if ($coordinates['west'] < 0 && $coordinates['east'] > 0 && $coordinates['west'] < $coordinates['east'])
				{
					$query->where('((i.longitude BETWEEN ' . $db->quote($coordinates['west']) . ' AND ' . $db->quote(0) . ')' .
						' OR (i.longitude BETWEEN ' . $db->quote(0) . ' AND ' . $db->quote($coordinates['east']) . '))');
				}
			}
		}

		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$cols = array('i.title', 'r.name', 'i.text', 'i.tags_search');
			$sql  = array();
			foreach ($cols as $col)
			{
				$sql[] = $db->quoteName($col) . ' LIKE '
					. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			}
			$query->where('(' . implode(' OR ', $sql) . ')');
		}

		// Group by
		$query->group(array('i.id'));

		// Add the list ordering clause.
		$ordering  = $this->state->get('list.ordering', 'i.created');
		$direction = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string  $query      The query.
	 * @param   integer $limitstart Offset.
	 * @param   integer $limit      The number of records.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @since 1.0.0
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->getDbo()->setQuery($query, $limitstart, $limit);

		return $this->getDbo()->loadObjectList('id');
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItems()
	{
		$items        = parent::getItems();
		$siteContacts = new Registry();
		$siteContacts->set('phones', $this->getState('params')->get('site_phones', array()));

		if (!empty($items))
		{
			$user       = Factory::getUser();
			$categories = $this->getCategories(array_unique(ArrayHelper::getColumn($items, 'catid')));

			$imagesHelper = new FieldTypesFilesHelper();
			foreach ($items as &$item)
			{
				// Set text
				$item->text = nl2br($item->text);

				// Set payment down
				$paymentDownDate = $item->payment_down;
				if ($paymentDownDate == '0000-00-00 00:00:00')
				{
					$paymentDown     = false;
					$paymentDownDate = Text::_('JNEVER');
				}
				else
				{
					$paymentDown = (Factory::getDate($paymentDownDate)->toUnix() < Factory::getDate()->toUnix());
				}
				$item->payment_down       = new stdClass();
				$item->payment_down->end  = $paymentDown;
				$item->payment_down->date = $paymentDownDate;

				// Set Edit
				$item->editLink = false;
				if (!$user->guest)
				{
					$userId   = $user->id;
					$asset    = 'com_prototype.item.' . $item->id;
					$editLink = Route::_(PrototypeHelperRoute::getFormRoute($item->id, $item->catid,
						$this->getState('return_view')));
					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$item->editLink = $editLink;
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $item->created_by)
						{
							$item->editLink = $editLink;
						}
					}
				}
				// Set Images
				$registry     = new Registry($item->images);
				$item->images = $registry->toArray();
				$item->images = $imagesHelper->getImages('content', 'images/prototype/items/' . $item->id, $item->images,
					array('text' => true, 'for_field' => false));
				$item->image  = (!empty($item->images) && !empty(reset($item->images)->src)) ?
					reset($item->images)->src : false;

				// Set author
				$author         = new stdClass();
				$author->type   = ($item->author_company) ? 'legal' : 'natural';
				$author->online = $item->author_online;
				$author->status = $item->author_status;
				if (!$item->author_company)
				{
					$author->id        = $item->author_id;
					$author->name      = $item->author_name;
					$author->signature = (!empty($item->author_job_name)) ? $item->author_job_name : '';
					$author->avatar    = $imagesHelper->getImage('avatar', 'images/profiles/' . $item->author_id,
						'media/com_profiles/images/no-avatar.jpg', false);
					$author->link      = Route::_(ProfilesHelperRoute::getProfileRoute($item->author_id));
					$author->contacts  = new Registry($item->author_contacts);
				}
				else
				{
					$author->id        = $item->author_job_id;
					$author->name      = $item->author_job_name;
					$author->signature = (!empty($item->author_position)) ? $item->author_position : $item->author_name;
					$author->avatar    = $imagesHelper->getImage('logo', 'images/companies/' . $item->author_job_id, false, false);
					$author->link      = Route::_(CompaniesHelperRoute::getCompanyRoute($item->author_job_id));
					$author->contacts  = new Registry($item->author_job_contacts);
				}
				if ($paymentDown)
				{
					$author->contacts = $siteContacts;
				}
				$author->contacts = $author->contacts->toArray();

				$item->author = $author;

				// Set map
				$item->map = (!empty($item->latitude) && !empty($item->longitude) &&
					$item->latitude !== '0.000000' && $item->longitude !== '0.000000') ? new Registry($item->map) : false;
				if ($item->map)
				{
					$item->map->set('link', Route::_(PrototypeHelperRoute::getMapRoute($item->catid) .
						'&center=' . $item->latitude . ',' . $item->longitude .
						'&zoom=' . $item->map->get('params')->zoom .
						'&item_id=' . $item->id));
				}

				// Set Tags
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_prototype.item', $item->id);

				// Set region
				$item->region_icon = $imagesHelper->getImage('icon', 'images/location/regions/' . $item->region_id, false, false);

				// Set Category
				$category       = !empty($categories[$item->catid]) ? $categories[$item->catid] : false;
				$item->category = $category;

				// Set Preset
				$presetKey    = trim($item->preset_price) . '|' . trim($item->preset_delivery) . '|' . trim($item->preset_object);
				$preset       = (!empty($category) && !empty($category->presets[$presetKey])) ? $category->presets[$presetKey] : false;
				$item->preset = $preset;

				// Set placemark
				$placemark               = new stdClass();
				$placemark->id           = $item->id;
				$placemark->title        = $item->title;
				$placemark->price        = $item->price;
				$placemark->preset_price = ($preset && $preset->price && !empty($preset->price->title)) ? $preset->price->title : '';
				$placemark->preset_icon  = ($preset && !empty($preset->icon)) ? $preset->icon : '';
				$placemark->show_price   = (!empty($item->price));
				$item->placemark         = $placemark;

				// Set render
				$render      = new stdClass();
				$displayData = array(
					'item'      => new Registry($item),
					'author'    => new Registry($author),
					'category'  => ($category) ? new Registry($category) : new  Registry(),
					'preset'    => ($preset) ? new Registry($preset) : new  Registry(),
					'placemark' => ($placemark) ? new Registry($placemark) : new  Registry(),
				);

				// List layout
				$layout           = new FileLayout('components.com_prototype.list.item.default');
				$render->listItem = $layout->render($displayData);

				// Balloon layout
				$layout          = new FileLayout('components.com_prototype.balloon.default');
				$render->balloon = $layout->render($displayData);

				// Author layout
				$layout         = new FileLayout('components.com_prototype.author.default');
				$render->author = $layout->render($displayData);

				// Map Placemark layout
				$layout               = new FileLayout('components.com_prototype.map.placemark.default');
				$render->mapPlacemark = $layout->render($displayData);

				// Map List item layout
				$layout              = new FileLayout('components.com_prototype.map.list.item.default');
				$render->mapListItem = $layout->render($displayData);

				$item->displayData = $displayData;
				$item->render      = $render;
			}
		}

		return $items;
	}

	/**
	 * Method to get Categories
	 *
	 * @param array $pks Categories Ids
	 *
	 * @return  array;
	 *
	 * @since 1.0.0
	 */
	protected function getCategories($pks = array())
	{
		$pks = (!is_array($pks)) ? (array) $pks : array_unique($pks);

		$categories = array();
		if (!empty($pks))
		{
			$getCategories = array();
			foreach ($pks as $pk)
			{
				if (isset($this->_categories[$pk]))
				{
					$categories[$pk] = $this->_categories[$pk];
				}
				elseif ($pk == 1)
				{
					$categories[$pk] = array();
				}
				elseif (!empty($pk))
				{
					$getCategories[] = $pk;
				}
			}
			if (!empty($getCategories))
			{
				try
				{
					$db    = Factory::getDbo();
					$query = $db->getQuery(true)
						->select(array('c.*', 'cp.title as parent_title', 'cp.level as parent_level'))
						->from($db->quoteName('#__prototype_categories', 'c'))
						->join('LEFT', '#__prototype_categories AS cp ON cp.id = c.parent_id')
						->where('c.id IN (' . implode(',', $getCategories) . ')')
						->order('c.lft ASC');
					$db->setQuery($query);
					$objects      = $db->loadObjectList('id');
					$imagesHelper = new FieldTypesFilesHelper();

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
							$configPresets[$key][$conf['value']] = $conf;
						}
					}
					foreach ($objects as $object)
					{

						$object->icon = $imagesHelper->getImage('icon', 'images/prototype/categories/' . $object->id, false, false);

						// Links
						$object->listLink   = Route::_(PrototypeHelperRoute::getListRoute($object->id));
						$object->addLink    = ($object->front_created > 0) ?
							Route::_(PrototypeHelperRoute::getFormRoute(null, $object->id)) : false;
						$object->mapLink    = Route::_(PrototypeHelperRoute::getMapRoute($object->id));
						$object->mapAddLink = ($object->front_created > 0) ?
							Route::_(PrototypeHelperRoute::getFormRoute(null, $object->id, 'map')) : false;

						// ParentLinks
						$object->parent_listLink = Route::_(PrototypeHelperRoute::getListRoute($object->parent_id));
						$object->parent_mapLink  = Route::_(PrototypeHelperRoute::getMapRoute($object->parent_id));

						$registry        = new Registry($object->presets);
						$object->presets = array();
						foreach ($registry->toArray() as $preset)
						{
							$preset['price']    = (!empty($configPresets['price'][$preset['price']])) ?
								(object) $configPresets['price'][$preset['price']] : false;
							$preset['delivery'] = (!empty($configPresets['delivery'][$preset['delivery']])) ?
								(object) $configPresets['delivery'][$preset['delivery']] : false;
							$preset['object']   = (!empty($configPresets['object'][$preset['object']])) ?
								(object) $configPresets['object'][$preset['object']] : false;

							$object->presets[$preset['key']] = (object) $preset;
						}

						$categories[$object->id]        = $object;
						$this->_categories[$object->id] = $object;
					}
				}
				catch (Exception $e)
				{
					$this->setError($e);
				}
			}
		}

		return $categories;
	}

	/**
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in \JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string  $key       The key of the user state variable.
	 * @param   string  $request   The name of the variable passed in a request.
	 * @param   string  $default   The default value for the variable if not found. Optional.
	 * @param   string  $type      Filter for the variable, for valid values see {@link \JFilterInput::clean()}. Optional.
	 * @param   boolean $resetPage If true, the limitstart in request is set to zero
	 *
	 * @return  mixed  The request user state.
	 *
	 * @since  1.0.0
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app       = Factory::getApplication();
		$set_state = $app->input->get($request, null, $type);
		$new_state = parent::getUserStateFromRequest($key, $request, $default, $type, $resetPage);
		if ($new_state == $set_state)
		{
			return $new_state;
		}
		$app->setUserState($key, $set_state);

		return $set_state;
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
					->select(array('c.*', 'cp.title as parent_title', 'cp.level as parent_level'))
					->from($db->quoteName('#__prototype_categories', 'c'))
					->join('LEFT', '#__prototype_categories AS cp ON cp.id = c.parent_id')
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

				$imagesHelper = new FieldTypesFilesHelper();
				$imagesFolder = 'images/prototype/categories/' . $data->id;
				$data->icon   = $imagesHelper->getImage('icon', $imagesFolder, false, false);

				// Links
				$data->listLink   = Route::_(PrototypeHelperRoute::getListRoute($data->id));
				$data->addLink    = ($data->front_created > 0) ?
					Route::_(PrototypeHelperRoute::getFormRoute(null, $data->id)) : false;
				$data->mapLink    = Route::_(PrototypeHelperRoute::getMapRoute($data->id));
				$data->mapAddLink = ($data->front_created > 0) ?
					Route::_(PrototypeHelperRoute::getFormRoute(null, $data->id, 'map')) : false;

				// Convert parameter fields to objects.
				$registry     = new Registry($data->attribs);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				// If no access, the layout takes some responsibility for display of limited information.
				$data->params->set('access-view', in_array($data->access, Factory::getUser()->getAuthorisedViewLevels()));

				// Convert metadata fields to objects.
				$data->metadata = new Registry($data->metadata);
				$data->metadata->set('image', $imagesHelper->getImage('meta', $imagesFolder, false, false));

				$this->_category[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_category[$pk] = false;
				}
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
						$imagesHelper     = new FieldTypesFilesHelper();
						$item->icon       = $imagesHelper->getImage('icon', 'images/prototype/categories/' . $item->id, false, false);
						$item->listLink   = Route::_(PrototypeHelperRoute::getListRoute($item->id));
						$item->addLink    = ($item->front_created > 0) ?
							Route::_(PrototypeHelperRoute::getFormRoute(null, $item->id)) : false;
						$item->mapLink    = Route::_(PrototypeHelperRoute::getMapRoute($item->id));
						$item->mapAddLink = ($item->front_created > 0) ?
							Route::_(PrototypeHelperRoute::getFormRoute(null, $item->id, 'map')) : false;

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

					$item->icon = $imagesHelper->getImage('icon', 'images/prototype/categories/' . $item->id, false, false);

					$item->listLink   = Route::_(PrototypeHelperRoute::getListRoute($item->id));
					$item->addLink    = ($item->front_created > 0) ?
						Route::_(PrototypeHelperRoute::getFormRoute(null, $item->id)) : false;
					$item->mapLink    = Route::_(PrototypeHelperRoute::getMapRoute($item->id));
					$item->mapAddLink = ($item->front_created > 0) ?
						Route::_(PrototypeHelperRoute::getFormRoute(null, $item->id, 'map')) : false;
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
	 * Increment the hit counter for the article.
	 *
	 * @param   integer $pk Optional primary key of the article to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 *
	 * @since  1.0.0
	 */
	public function hit($pk = 0)
	{
		$app      = Factory::getApplication();
		$hitcount = $app->input->getInt('hitcount', 1);
		if ($hitcount)
		{
			$pk    = (!empty($pk)) ? $pk : (int) $this->getState('filter.item_id');
			$table = Table::getInstance('Items', 'PrototypeTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}