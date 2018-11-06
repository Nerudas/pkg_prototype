<?php
/**
 * @package    Sitemap - Prototype Plugin
 * @version    1.3.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;


class plgSitemapPrototype extends CMSPlugin
{

	/**
	 * Urls array
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	protected $_urls = null;

	/**
	 * Method to get Links array
	 *
	 * @return array
	 *
	 * @since 1.1.1
	 */
	public function getUrls()
	{
		if ($this->_urls === null)
		{

			// Include route helper
			JLoader::register('PrototypeHelperRoute', JPATH_SITE . '/components/com_prototype/helpers/route.php');

			$db   = Factory::getDbo();
			$user = Factory::getUser(0);


			// Get items
			$query = $db->getQuery(true)
				->select(array('c.id'))
				->from($db->quoteName('#__prototype_categories', 'c'))
				->where('c.state= 1')
				->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->group('c.id')
				->order('c.lft ASC');

			$itemsQuery = $db->getQuery(true)
				->select('si.id')
				->from($db->quoteName('#__prototype_items', 'si'))
				->where('si.catid = c.id')
				->where('i.state= 1')
				->where('i.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->order('si.created DESC')
				->setLimit(1);
			$query->select('i.created as modified')
				->join('LEFT', '#__prototype_items AS i ON i.id = (' . (string) $itemsQuery . ')');

			$db->setQuery($query);
			$categories = $db->loadObjectList('id');

			$changefreq = $this->params->def('changefreq', 'weekly');
			$priority   = $this->params->def('priority', '0.5');

			$list_urls = array();
			$map_urls  = array();
			foreach ($categories as $category)
			{
				// List
				$list             = new stdClass();
				$list->loc        = PrototypeHelperRoute::getListRoute($category->id);
				$list->changefreq = $changefreq;
				$list->priority   = $priority;
				if (!empty($category->modified))
				{
					$list->lastmod = $category->modified;
				}

				$list_urls[] = $list;

				// Map
				$map             = new stdClass();
				$map->loc        = PrototypeHelperRoute::getMapRoute($category->id);
				$map->changefreq = $changefreq;
				$map->priority   = $priority;
				if (!empty($category->modified))
				{
					$map->lastmod = $category->modified;
				}

				$map_urls[] = $map;
			}

			$this->_urls = array_merge($list_urls, $map_urls);
		}

		return $this->_urls;
	}
}