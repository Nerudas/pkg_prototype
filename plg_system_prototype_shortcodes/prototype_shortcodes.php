<?php
/**
 * @package    System - Prototype Shortcodes Plugin
 * @version    1.4.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Document\Renderer\Html\HeadRenderer;

class plgSystemPrototype_Shortcodes extends CMSPlugin
{

	/**
	 * Listener for the `onAfterRender` event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		$app = Factory::getApplication();
		if ($app->isSite())
		{
			$body = JResponse::getBody();
			preg_match_all('/{prototype id="(.*?)" layout="(.*?)"}/', $body, $matches);
			$shortcodes = (!empty($matches[0])) ? $matches[0] : array();
			$ids        = (!empty($matches[1])) ? $matches[1] : array();
			$layouts    = (!empty($matches[2])) ? $matches[2] : array();

			if (!empty($ids))
			{
				JLoader::register('PrototypeHelperRoute', JPATH_SITE . '/components/com_prototype/helpers/route.php');

				// Load Language
				$language = Factory::getLanguage();
				$language->load('com_prototype', JPATH_SITE, $language->getTag());

				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_prototype/models', 'PrototypeModel');
				$model = BaseDatabaseModel::getInstance('Items', 'PrototypeModel', array('ignore_request' => true));
				$model->setState('list.limit', 0);
				$model->setState('filter.published', 1);
				$model->setState('filter.item_id', $ids);
				$items = $model->getItems();

				if (!empty($items))
				{
					$addBlocks = '';
					if (!preg_match('/data-prototype-shortcodes-author/', $body))
					{
						$addBlocks .= LayoutHelper::render('components.com_prototype.shortcodes.author');
					}
					if (!preg_match('/data-prototype-shortcodes-balloon/', $body))
					{
						$addBlocks .= LayoutHelper::render('components.com_prototype.shortcodes.balloon');
					}
					$addBlocks .= '</body>';
					$body      = str_replace('</body>', $addBlocks, $body);

					$doc = Factory::getDocument();
					LayoutHelper::render('components.com_prototype.shortcodes.head');
					$headRender = new HeadRenderer($doc);
					$newHead    = '<head>' . PHP_EOL . $headRender->render('') . PHP_EOL . '</head>';

					$body = preg_replace('|<head>(.*)</head>|si', $newHead, $body);
				}

				foreach ($shortcodes as $key => $shortcode)
				{
					$id     = (!empty($ids[$key])) ? $ids[$key] : false;
					$layout = (!empty($layouts[$key])) ? $layouts[$key] : false;
					$item   = ($id && !empty($items[$id])) ? $items[$id] : false;

					$replace = ($id && $layout && $item) ?
						LayoutHelper::render('components.com_prototype.shortcodes.item.' . $layout, $item->displayData) : '';

					$body = str_replace($shortcode, $replace, $body);
				}

				JResponse::setBody($body);
			}
		}
	}
}