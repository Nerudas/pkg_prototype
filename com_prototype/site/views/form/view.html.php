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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class PrototypeViewForm extends HtmlView
{
	/**
	 * The JForm object
	 *
	 * @var  JForm
	 *
	 * @since  1.0.0
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Current Category data
	 *
	 * @var    object
	 *
	 * @since  1.0.0
	 */
	protected $category = array();

	/**
	 * Category parent data
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	protected $parent = array();

	/**
	 * The children array
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $children;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  JObject
	 *
	 * @since  1.0.0
	 */
	protected $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed A string if successful, otherwise an Error object.
	 *
	 * @throws Exception
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		// Get model data.
		$this->form     = $this->get('Form');
		$this->item     = $this->get('Item');
		$this->state    = $this->get('State');
		$this->category = $this->get('Category');
		$this->parent   = $this->get('Parent');
		$this->children = $this->get('Children');
		$this->link     = $this->category->formLink;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}
		// Set Layout
		$params = $this->state->get('params');
		$active = $app->getMenu()->getActive();

		$layout = ($active && !empty($active->query['layout']) &&
			strpos($active->link, 'view=form') &&
			strpos($active->link, '&catid=' . (string) $this->state->get('category.id')) &&
			strpos($active->link, '&id=' . (string) $this->state->get('item.id'))
		) ? $active->query['layout'] : $params->get('form_layout', 'default');


		if (!empty($this->children))
		{
			$layout .= '_children';
		}
		else
		{
			// Check actions
			$authorised = (empty($this->item->id)) ?
				$user->authorise('core.create', 'com_prototype') && $this->category->front_created > 0 :
				$user->authorise('core.edit', 'com_prototype.item.' . $this->item->id) ||
				($user->authorise('core.edit.own', 'com_prototype.item.' . $this->item->id)
					&& $this->item->created_by == $user->id);

			if (!$authorised && $user->guest)
			{
				$login = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()));
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
				$app->redirect($login, 403);
			}
			elseif (!$authorised)
			{
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);

				return false;
			}
		}

		$category       = $this->category;
		$extraFields    = $this->form->getGroup('extra');
		$categoryFields = (!empty($category->fields)) ? $category->fields : array();
		foreach ($extraFields as $extraField)
		{
			$name = $extraField->getAttribute('name');
			if (empty($categoryFields[$name]))
			{
				$this->form->removeField($name, 'extra');
			}
		}

		$this->setLayout($layout);

		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function _prepareDocument()
	{
		$app        = Factory::getApplication();
		$category   = $this->category;
		$breadcrumb = ($this->item->id) ? Text::_('COM_PROTOTYPE_ITEM_EDIT') : Text::_('COM_PROTOTYPE_ITEM_ADD');
		$pathway    = $app->getPathway();
		$menus      = $app->getMenu();
		$menu       = $menus->getActive();
		$id         = (int) @$menu->query['id'];

		// If the menu item does not concern this contact
		if ($menu && ($menu->query['option'] !== 'com_prototype' || $menu->query['view'] !== 'form' ||
				$menu->query['catid'] !== $category->id || $id != $this->item->id))
		{
			$path   = array();
			$path[] = array('title' => $breadcrumb, 'link' => '');
			$parent = $this->category;
			while ($parent && $parent->id > 1 &&
				($menu->query['option'] !== 'com_prototype' || $menu->query['view'] !== 'list' || $id != $parent->id))
			{
				$path[] = array('title' => $parent->title, 'link' => $parent->listLink);
				$parent = $this->getModel()->getParent($parent->id);
			}

			foreach (array_reverse($path) as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Set pathway title
		$title = array();
		foreach ($pathway->getPathWay() as $value)
		{
			$title[] = $value->name;
		}
		$title = implode(' / ', $title);

		$this->document->setTitle($title);
		$this->document->setMetadata('robots', 'noindex');
	}

	/**
	 * Returns the categories array
	 *
	 * @return  mixed  array
	 *
	 * @since  1.0.0
	 */
	public function getChildren()
	{
		if (!is_array($this->children))
		{
			$this->children = $this->get('Children');
		}

		return $this->children;
	}
}