<?php
/**
 * @package    Prototype Component
 * @version    1.4.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Utilities\ArrayHelper;

class PrototypeModelCategories extends ListModel
{
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
				'parent_id', 'i.parent_id',
				'lft', 'i.lft',
				'rgt', 'i.rgt',
				'level', 'i.level',
				'path', 'i.path',
				'alias', 'i.alias',
				'attribs', 'i.attribs',
				'front_created', 'i.front_created',
				'state', 'i.state', 'published',
				'metakey', 'i.metakey',
				'metadesc', 'i.metadesc',
				'access', 'i.access',
				'metadata', 'i.metadata',
				'tags_search', 'i.tags_search',
				'tags_map', 'i.tags_map',
				'items_tags', 'i.items_tags',
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

		$front_created = $this->getUserStateFromRequest($this->context . '.filter.front_created', 'filter_front_created', '');
		$this->setState('filter.front_created', $front_created);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$tags = $this->getUserStateFromRequest($this->context . '.filter.tags', 'filter_tags', '');
		$this->setState('filter.tags', $tags);

		$tags = $this->getUserStateFromRequest($this->context . '.filter.tags', 'filter_tags', '');
		$this->setState('filter.parent', $tags);


		// List state information.
		$ordering  = empty($ordering) ? 'c.lft' : $ordering;
		$direction = empty($direction) ? 'asc' : $direction;
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
		$id .= ':' . $this->getState('filter.front_created');
		$id .= ':' . $this->getState('filter.parent');
		$id .= ':' . serialize($this->getState('filter.tag'));

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
			->select('c.*')
			->from($db->quoteName('#__prototype_categories', 'c'))
			->where($db->quoteName('c.alias') . ' <> ' . $db->quote('root'));

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = c.access');

		// Filter by access level.
		$access = $this->getState('filter.access');
		if (is_numeric($access))
		{
			$query->where('с.access = ' . (int) $access);
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('c.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(c.state = 0 OR c.state = 1)');
		}

		// Filter by front_created
		$front_created = $this->getState('filter.front_created');
		if (is_numeric($front_created))
		{
			$query->where('c.front_created = ' . (int) $front_created);
		}

		// Filter by parent state
		$parent = $this->getState('filter.parent');
		if (is_numeric($parent))
		{
			// Create a subquery for the sub-items list
			$subQuery = $db->getQuery(true)
				->select('sub.id')
				->from('#__prototype_categories as sub')
				->join('INNER', '#__prototype_categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
				->where('this.id = ' . (int) $parent);

			// Add the subquery to the main query
			$query->where('(c.parent_id = ' . (int) $parent . ' OR ' . 'c.id =' . (int) $parent .
				' OR c.parent_id IN (' . (string) $subQuery . '))');
		}

		// Filter by a single or group of tags.
		$tags = $this->getState('filter.tags');
		if (is_array($tags))
		{
			$tags = ArrayHelper::toInteger($tags);
			$tags = implode(',', $tags);
			if (!empty($tags))
			{
				$query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('c.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_prototype.category'))
					->where($db->quoteName('tagmap.tag_id') . ' IN (' . $tags . ')');
			}
		}

		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('id = ' . (int) substr($search, 3));
			}
			else
			{
				$cols = array('c.title', 'c.alias');
				$sql  = array();
				foreach ($cols as $col)
				{
					$sql[] = $db->quoteName($col) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}
				$query->where('(' . implode(' OR ', $sql) . ')');

			}
		}

		// Add the list ordering clause.
		$ordering  = $this->state->get('list.ordering', 'c.lft');
		$direction = $this->state->get('list.direction', 'asc');
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
			foreach ($items as &$item)
			{
				// Get Tags
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_prototype.category', $item->id);
			}
		}

		return $items;
	}
}