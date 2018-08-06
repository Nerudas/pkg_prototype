<?php
/**
 * @package    Prototype Component
 * @version    1.1.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Factory;

class PrototypeModelCategories extends ListModel
{
	/**
	 * Placemarks
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_placemarks = array();

	/**
	 * Placemarks layouts
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_placemarksLayouts = array();

	/**
	 * Palcemarks Layouts path
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_layoutsPaths = null;

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
				'fields', 'i.fields',
				'front_created', 'i.front_created',
				'placemark_id', 'i.placemark_id',
				'icon', 'i.icon',
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
			$placemarks = $this->getPlacemarks(array_unique(ArrayHelper::getColumn($items, 'placemark_id')));

			foreach ($items as &$item)
			{
				// Get Tags
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_prototype.category', $item->id);


				$item->placemark = new Registry((!empty($placemarks[$item->placemark_id])) ?
					$placemarks[$item->placemark_id] : array());

				$placemark_layout     = $this->getPlacemarkLayout($item->placemark->get('layout', 'default'));
				$layoutData           = array(
					'item'      => new Registry($item),
					'extra'     => new Registry(array()),
					'category'  => new Registry($item),
					'extra_filter'     => new Registry(array()),
					'placemark' => $item->placemark,
				);
				$item->placemark_demo = $placemark_layout->render($layoutData);
			}
		}

		return $items;
	}


	/**
	 * Method to get Placemarks
	 *
	 * @param array $pks PlaceMarks Ids
	 *
	 * @return  array;
	 *
	 * @since 1.0.0
	 */
	protected function getPlacemarks($pks = array())
	{
		$pks = (!is_array($pks)) ? (array) $pks : array_unique($pks);

		$placemarks = array();
		if (!empty($pks))
		{
			$getPlacemarks = array();
			foreach ($pks as $pk)
			{
				if (isset($this->_placemarks[$pk]))
				{
					$placemarks[$pk] = $this->_placemarks[$pk];
				}
				elseif (!empty($pk))
				{
					$getPlacemarks[] = $pk;
				}
			}
			if (!empty($getPlacemarks))
			{
				try
				{
					$db    = Factory::getDbo();
					$query = $db->getQuery(true)
						->select('*')
						->from($db->quoteName('#__prototype_placemarks', 'p'))
						->where('p.id IN (' . implode(',', $getPlacemarks) . ')');
					$db->setQuery($query);
					$objects = $db->loadObjectList('id');
					foreach ($objects as $object)
					{
						// Convert the images field to an array.
						$registry       = new Registry($object->images);
						$object->images = $registry->toArray();
						$object->image  = (!empty($object->images) && !empty(reset($object->images)['src'])) ?
							reset($object->images)['src'] : false;

						$placemarks[$object->id]        = $object;
						$this->_placemarks[$object->id] = $object;
					}
				}
				catch (Exception $e)
				{
					$this->setError($e);
				}
			}
		}


		return $placemarks;
	}

	/**
	 * Method to get Layouts paths
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getLayoutsPaths()
	{
		if (!is_array($this->_layoutsPaths))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('template')
				->from('#__template_styles')
				->where('client_id = 0')
				->order('home DESC');
			$db->setQuery($query);
			$templates   = $db->loadColumn();
			$language = Factory::getLanguage();

			$layoutPaths = array();
			foreach (array_unique($templates) as $template)
			{
				$layoutPaths[] = JPATH_ROOT . '/templates/' . $template . '/html/layouts';
				$language->load('tpl_' . $template, JPATH_SITE, $language->getTag(), true);
			}
			$layoutPaths[] = JPATH_ROOT . '/layouts';

			$this->_layoutsPaths = $layoutPaths;
		}

		return $this->_layoutsPaths;
	}

	/**
	 * Method to get Placemarks Layout
	 *
	 * @param string $layoutName Layout name
	 *
	 * @return  FileLayout;
	 *
	 * @since 1.0.0
	 */
	protected function getPlacemarkLayout($layoutName)
	{
		if (isset($this->_placemarkLayouts[$layoutName]))
		{
			return $this->_placemarkLayouts[$layoutName];
		}

		$key = $layoutName;

		$layoutPaths = $this->getlayoutsPaths();
		if (!JPath::find($layoutPaths, 'components/com_prototype/placemarks/' . $layoutName . '.php'))
		{
			$layoutName = 'default';
		}

		$layoutID = 'components.com_prototype.placemarks.' . $layoutName;
		$layout   = new FileLayout($layoutID);
		$layout->setIncludePaths($layoutPaths);

		$this->_placemarkLayouts[$key] = $layout;

		return $this->_placemarkLayouts[$key];
	}

}