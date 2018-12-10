<?php
/**
 * @package    Prototype Component
 * @version    1.3.8
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Application\SiteApplication;

class PrototypeViewItem extends HtmlView
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
	 * The actions the user is authorised to perform
	 *
	 * @var  JObject
	 *
	 * @since  1.0.0
	 */
	protected $canDo;

	/**
	 * An author profile
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $author;

	/**
	 * An author profile information
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $author_information;

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
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		$doc = Factory::getDocument();

		$catid = $this->form->getValue('catid', '', '');
		if ($catid > 1 && $category = $this->getModel()->getCategory($catid))
		{
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
		}
		else
		{
			$this->form->removeGroup('extra');
		}

		$doc->addScriptDeclaration('function categoryHasChanged(element) {
			var cat = jQuery(element);
			Joomla.loadingLayer(\'show\');
			jQuery(\'input[name=task]\').val(\'item.reload\');
			element.form.submit();
		}');

		$this->author             = false;
		$this->author_information = false;
		$created_by               = $this->form->getValue('created_by', '', '');
		if (!empty($created_by))
		{
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_profiles/models', 'ProfilesModel');
			$profileModel             = BaseDatabaseModel::getInstance('Profile', 'ProfilesModel', array('ignore_request' => true));
			$this->author             = $profileModel->getItem($created_by);
			$this->author_information = $profileModel->getInformation($this->author);

			$language = Factory::getLanguage();
			$language->load('com_users', JPATH_ADMINISTRATOR, $language->getTag(), true);
		}

		$doc->addScriptDeclaration('function authorHasChanged(element) {
				Joomla.loadingLayer(\'show\');
				jQuery(\'input[name=task]\').val(\'item.reload\');
				jQuery(element).closest(\'form\').submit();
		}');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Returns the categories array
	 *
	 * @return  mixed  array
	 *
	 * @since  1.0.0
	 */
	public function getCategories()
	{
		if (!is_array($this->categories))
		{
			$this->categories = $this->get('Categories');
		}

		return $this->categories;
	}

	/**
	 * Add the type title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$isNew      = ($this->item->id == 0);
		$this->user = Factory::getUser();
		$canDo      = PrototypeHelper::getActions('com_prototype', 'item', $this->item->id);

		if ($isNew)
		{
			// Add title
			JToolBarHelper::title(
				TEXT::_('COM_PROTOTYPE') . ': ' . TEXT::_('COM_PROTOTYPE_ITEM_ADD'), 'clock'
			);
			$this->document->setTitle(TEXT::_('COM_PROTOTYPE_ITEM_ADD'));

			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::apply('item.apply');
				JToolbarHelper::save('item.save');
				JToolbarHelper::save2new('item.save2new');
			}
		}
		// Edit
		else
		{
			// Add title
			JToolBarHelper::title(
				TEXT::_('COM_PROTOTYPE') . ': ' . TEXT::_('COM_PROTOTYPE_ITEM_EDIT'), 'clock'
			);
			$this->document->setTitle($this->item->title);

			// Can't save the record if it's and editable
			if ($canDo->get('core.edit'))
			{
				JToolbarHelper::apply('item.apply');
				JToolbarHelper::save('item.save');
				JToolbarHelper::save2new('item.save2new');
			}

			// If checked out, we can still save
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('item.save2copy');
			}

			// Go to page
			JLoader::register('PrototypeHelperRoute', JPATH_SITE . '/components/com_prototype/helpers/route.php');
			$siteRouter = SiteApplication::getRouter();

			$listLink = $siteRouter->build(PrototypeHelperRoute::getListRoute($this->item->catid) .
				'&item_id=' . $this->item->id)->toString();
			$listLink = str_replace('administrator/', '', $listLink);

			$mapLink = $siteRouter->build(PrototypeHelperRoute::getMapRoute($this->item->catid .
				'&center=' . $this->item->map['params']['latitude'] . ',' . $this->item->map['params']['longitude'] .
				'&zoom=' . $this->item->map['params']['zoom'] .
				'&item_id=' . $this->item->id))->toString();
			$mapLink = str_replace('administrator/', '', $mapLink);

			$toolbar = JToolBar::getInstance('toolbar');
			$toolbar->appendButton('Custom', '<div class="btn-group">' .
				'<a href="' . $listLink . '" class="btn btn-small btn-primary"
					target="_blank">' . Text::_('COM_PROTOTYPE_GO_TO_LIST') . '</a>' .
				'<a href="' . $mapLink . '" class="btn btn-small btn-primary"
					target="_blank">' . Text::_('COM_PROTOTYPE_GO_TO_MAP') . '</a>' .
				'</div>', 'goTo');

			// Prolong
			if ($canDo->get('core.edit'))
			{
				JToolbarHelper::custom('item.prolong_1m', 'prolong_1m', 'prolong_1m',
					'COM_PROTOTYPE_ITEM_PROLONG_1M', false);
				JToolbarHelper::custom('item.prolong_1w', 'prolong_1w', 'prolong_1w',
					'COM_PROTOTYPE_ITEM_PROLONG_1W', false);
				JToolbarHelper::custom('item.prolong_3d', 'prolong_3d', 'prolong_3d',
					'COM_PROTOTYPE_ITEM_PROLONG_3D', false);
			}
		}

		JToolbarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		JToolbarHelper::divider();
	}
}