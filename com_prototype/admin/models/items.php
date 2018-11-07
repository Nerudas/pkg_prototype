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
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

JLoader::register('FieldTypesFilesHelper', JPATH_PLUGINS . '/fieldtypes/files/helper.php');

class PrototypeModelItems extends ListModel
{
	/**
	 * Categories
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_categories = array();

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
				'html', 'i.html',
				'images', 'i.images',
				'state', 'i.state',
				'created', 'i.created',
				'created_by', 'i.created_by',
				'publish_down', 'i.publish_down',
				'payment_number', 'i.payment_number',
				'placemark_id', 'i.placemark_id',
				'balloon_layout', 'i.balloon_layout',
				'map', 'i.map',
				'latitude', 'i.latitude',
				'longitude', 'i.longitude',
				'attribs', 'i.attribs',
				'metakey',
				'metadesc', 'i.metadesc',
				'access', 'i.access',
				'hits', 'i.hits',
				'region', 'i.region',
				'metadata', 'i.metadata',
				'tags_search', 'i.tags_search',
				'tags_map', 'i.tags_map',
				'extra', 'i.extra',
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
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by');
		$this->setState('filter.created_by', $created_by);

		$region = $this->getUserStateFromRequest($this->context . '.filter.region', 'filter_region', '');
		$this->setState('filter.region', $region);

		$category = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '');
		$this->setState('filter.category', $category);

		$publish_down = $this->getUserStateFromRequest($this->context . '.filter.publish_down', 'filter_publish_down', '');
		$this->setState('filter.publish_down', $publish_down);

		$payment_number = $this->getUserStateFromRequest($this->context . '.filter.payment_number', 'filter_payment_number', '');
		$this->setState('filter.payment_number', $payment_number);

		// List state information.
		$ordering  = empty($ordering) ? 'i.created' : $ordering;
		$direction = empty($direction) ? 'desc' : $direction;
		parent::populateState($ordering, $direction);
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.region');
		$id .= ':' . $this->getState('filter.category');
		$id .= ':' . $this->getState('filter.publish_down');
		$id .= ':' . $this->getState('filter.payment_number');

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
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('i.*')
			->from($db->quoteName('#__prototype_items', 'i'));

		// Join over the author.
		$offline      = (int) ComponentHelper::getParams('com_profiles')->get('offline_time', 5) * 60;
		$offline_time = Factory::getDate()->toUnix() - $offline;
		$query->select(array(
			'author.id as author_id',
			'author.name as author_name',
			'author.status as author_status',
			'(session.time IS NOT NULL) AS author_online',
			'(company.id IS NOT NULL) AS author_job',
			'company.id as author_job_id',
			'company.name as author_job_name',
			'employees.position as  author_position'
		))
			->join('LEFT', '#__profiles AS author ON author.id = i.created_by')
			->join('LEFT', '#__session AS session ON session.userid = author.id AND session.time > ' . $offline_time)
			->join('LEFT', '#__companies_employees AS employees ON employees.user_id = author.id AND ' .
				$db->quoteName('employees.key') . ' = ' . $db->quote(''))
			->join('LEFT', '#__companies AS company ON company.id = employees.company_id AND company.state = 1');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = i.access');

		// Join over the regions.
		$query->select(array('r.id as region_id', 'r.name as region_name'))
			->join('LEFT', '#__location_regions AS r ON r.id = i.region');


		// Filter by access level.
		$access = $this->getState('filter.access');
		if (is_numeric($access))
		{
			$query->where('i.access = ' . (int) $access);
		}

		// Filter by region
		$region = $this->getState('filter.region');
		if (!empty($region))
		{
			$query->where($db->quoteName('i.region') . ' = ' . $db->quote($region));
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('i.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(i.state = 0 OR i.state = 1)');
		}

		// Filter by payment number
		$payment_number = trim($this->getState('filter.payment_number'));
		if (!empty($payment_number))
		{
			$query->where($db->quoteName('i.payment_number') . ' = ' . $db->quote($payment_number));
		}

		$publish_down = $this->getState('filter.publish_down');
		if (!empty($publish_down))
		{
			$nullDate = $db->getNullDate();
			if ($publish_down == 'never')
			{
				$query->where($db->quoteName('i.publish_down') . ' = ' . $db->Quote($nullDate));
			}
			else
			{
				$now = Factory::getDate($publish_down)->toSql();
				$query->where($db->quoteName('i.publish_down') . ' != ' . $db->Quote($nullDate));
				$query->where($db->quoteName('i.publish_down') . '  < ' . $db->Quote($now));
			}
		}


		// Filter by created_by
		$created_by = $this->getState('filter.created_by');
		if (!empty($created_by))
		{
			$query->where('i.created_by = ' . (int) $created_by);
		}

		// Filter by category
		$category = $this->getState('filter.category');
		if ($category > 1)
		{
			$query->where('i.catid = ' . (int) $category);

		}

		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('i.id = ' . (int) substr($search, 3));
			}
			else
			{
				$cols = array('i.title', 'r.name', 'i.html', 'author.name', 'i.extra');
				$sql  = array();
				foreach ($cols as $col)
				{
					$sql[] = $db->quoteName($col) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}
				$query->where('(' . implode(' OR ', $sql) . ')');
			}
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
		$items = parent::getItems();
		if (!empty($items))
		{
			$categories = $this->getCategories(array_unique(ArrayHelper::getColumn($items, 'catid')));
			$imagesHelper         = new FieldTypesFilesHelper();
			foreach ($items as &$item)
			{
				$author_avatar       = $imagesHelper->getImage('avatar', 'images/profiles/' . $item->author_id,
					'media/com_profiles/images/no-avatar.jpg', false);
				$item->author_avatar = Uri::root(true) . '/' . $author_avatar;

				// Get Tags
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_prototype.item', $item->id);
				// Get Category
				$item->category = new Registry((!empty($categories[$item->catid])) ? $categories[$item->catid] : array());
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
						->where('c.id IN (' . implode(',', $getCategories) . ')');
					$db->setQuery($query);
					$objects = $db->loadObjectList('id');
					foreach ($objects as $object)
					{
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
}