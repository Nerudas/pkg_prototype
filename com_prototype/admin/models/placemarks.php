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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Factory;

jimport('joomla.filesystem.file');

class PrototypeModelPlacemarks extends ListModel
{
	/**
	 * Item layouts
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_itemLayouts = array();

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
				'id', 'p.id',
				'title', 'p.title',
				'images', 'p.images',
				'state', 'p.state', 'published',
				'access', 'p.access',
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

		// List state information.
		$ordering  = empty($ordering) ? 'p.title' : $ordering;
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
			->select('p.*')
			->from($db->quoteName('#__prototype_placemarks', 'p'));

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = p.access');


		// Filter by access level.
		$access = $this->getState('filter.access');
		if (is_numeric($access))
		{
			$query->where('p.access = ' . (int) $access);
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('p.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(p.state = 0 OR p.state = 1)');
		}

		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('p.id = ' . (int) substr($search, 3));
			}
			else
			{
				$cols = array('p.title');
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
		$query->group(array('p.id'));

		// Add the list ordering clause.
		$ordering  = $this->state->get('list.ordering', 'p.title');
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
				// Convert the images field to an array.
				$registry     = new Registry($item->images);
				$item->images = $registry->toArray();
				$item->image  = (!empty($item->images) && !empty(reset($item->images)['src'])) ?
					reset($item->images)['src'] : false;

				$layout     = $this->getItemLayout($item->layout);
				$layoutData = array(
					'item'         => new Registry($item),
					'extra'        => new Registry(array()),
					'category'     => new Registry($item),
					'extra_filter' => new Registry(array()),
					'placemark'    => new Registry($item),
				);
				$item->demo = $layout->render($layoutData);
			}
		}

		return $items;
	}

	/**
	 * Get the filter form
	 *
	 * @param string $layoutName Layout name
	 *
	 * @return  FileLayout;
	 *
	 * @since 1.0.0
	 */
	protected function getItemLayout($layoutName)
	{
		if (isset($this->_itemLayouts[$layoutName]))
		{
			return $this->_itemLayouts[$layoutName];
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

		$this->_itemLayouts[$key] = $layout;

		return $this->_itemLayouts[$key];
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
			$templates = $db->loadColumn();
			$language  = Factory::getLanguage();

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
}