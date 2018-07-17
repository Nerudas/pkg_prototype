<?php
/**
 * @package    Prototype Component
 * @version    1.0.8
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::register('PrototypeModelItems', JPATH_SITE . '/components/com_prototype/models/items.php');

class PrototypeModelList extends PrototypeModelItems
{

	/**
	 * Item offset
	 *
	 * @var    object
	 *
	 * @since  1.0.0
	 */
	protected $_item_offset = array();

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   1.0.0
	 */
	public function getStart()
	{
		$start = parent::getStart();

		return (!empty($start)) ? $start : $this->getListStart();
	}


	/**
	 * Method to get post offset
	 *
	 * @return int
	 * @since  1.0.0
	 */
	public function getListStart()
	{
		$app        = Factory::getApplication();
		$limitstart = $this->getState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$pk         = $this->getState('item.id', $app->input->getInt('item_id', 0));

		if (!empty($limitstart) || empty($pk))
		{
			return $limitstart;
		}

		if (!isset($this->_item_offset[$pk]))
		{
			try
			{
				$params = $this->getState('params');
				$limit  = $this->getState('list.limit', $params->get('items_limit', 10, 'uint'));

				$db    = Factory::getDbo();
				$query = $this->getListQuery()
					->clear('select')
					->clear('limit')
					->clear('offset')
					->select('i.id');

				$db->setQuery($query);
				$itemsID    = $db->loadColumn();
				$itemOffset = 0;
				if (in_array($pk, $itemsID))
				{
					foreach ($itemsID as $id)
					{
						if ($id == $pk)
						{
							break;
						}
						$itemOffset++;
					}
				}
				$page   = floor(($itemOffset / $limit) + 1);
				$offset = ($page * $limit) - $limit;

				$this->_item_offset[$pk] = $offset;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_item_offset[$pk] = 0;
			}
		}

		return $this->_item_offset[$pk];
	}

	/**
	 * Method to get a \JPagination object for the data set.
	 *
	 * @return  \JPagination  A \JPagination object for the data set.
	 *
	 * @since   1.0.0
	 */
	public function getPagination()
	{
		$pagination = parent::getPagination();
		$pk         = $this->getState('item.id', Factory::getApplication()->input->getInt('item_id', 0));;
		if (!empty($pk))
		{
			$pagination->setAdditionalUrlParam('item_id', 0);
		}

		return $pagination;
	}
}