<?php
/**
 * @package    Prototype Component
 * @version    1.2.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class PrototypeViewPlacemarks extends HtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 *
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 *
	 * @since  1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	public $activeFilters;

	/**
	 * Display the view
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed A string if successful, otherwise an Error object.
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		PrototypeHelper::addSubmenu('placemarks');

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->state         = $this->get('State');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		return parent::display($tpl);
	}


	/**
	 * Add the extension title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function addToolbar()
	{
		$user  = Factory::getUser();
		$canDo = PrototypeHelper::getActions('com_prototype', 'placemarks');

		JToolBarHelper::title(Text::_('COM_PROTOTYPE') . ': ' . Text::_('COM_PROTOTYPE_PLACEMARKS'), 'clock');
		$this->document->setTitle(TEXT::_('COM_PROTOTYPE_PLACEMARKS'));

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('placemark.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('placemark.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('placemarks.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('placemarks.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'placemarks.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('placemarks.trash');
		}

		if ($user->authorise('core.admin', 'com_prototype') || $user->authorise('core.options', 'com_prototype'))
		{
			JToolbarHelper::preferences('com_prototype');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since  1.0.0
	 */
	protected function getSortFields()
	{
		return [
			'p.state'      => Text::_('JSTATUS'),
			'p.id'         => Text::_('JGRID_HEADING_ID'),
			'p.title'      => Text::_('JGLOBAL_TITLE'),
			'access_level' => Text::_('JGRID_HEADING_ACCESS'),
		];
	}
}