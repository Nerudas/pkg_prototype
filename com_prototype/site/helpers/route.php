<?php
/**
 * @package    Prototype Component
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\RouteHelper;
use Joomla\CMS\Factory;

class PrototypeHelperRoute extends RouteHelper
{
	/**
	 * Fetches the list route
	 *
	 * @param   int $catid Category ID
	 *
	 * @return  string
	 *
	 * @since  1.0.0n
	 */
	public static function getListRoute($catid = 1)
	{
		return 'index.php?option=com_prototype&view=list&id=' . $catid;
	}

	/**
	 * Fetches the item route
	 *
	 * @param   int $catid Category ID
	 * @param   int $id    Item ID
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public static function getItemRoute($id = null, $catid = 1)
	{
		$link = 'index.php?option=com_prototype&view=item';
		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}
		if (!empty($catid))
		{
			$link .= '&catid=' . $catid;
		}

		return $link;
	}

	/**
	 * Fetches the form route
	 *
	 * @param  int $id    Item ID
	 * @param  int $catid Category ID
	 * @param null $return_view
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public static function getFormRoute($id = null, $catid = 1, $return_view = null)
	{
		$link = 'index.php?option=com_prototype&view=form&catid=' . $catid;
		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		$app = Factory::getApplication();
		if (!empty($return_view))
		{
			$link .= '&return_view=' . $return_view;
		}
		elseif ($app->input->get('option') == 'com_prototype' && $app->input->get('view') == 'form'
			&& !empty($app->input->get('return_view')))
		{
			$link .= '&return_view=' . $app->input->get('return_view');
		}

		return $link;
	}

	/**
	 * Fetches the map route
	 *
	 * @param   int $catid Category ID
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public static function getMapRoute($catid = 1)
	{
		return 'index.php?option=com_prototype&view=map&id=' . $catid;
	}
}
