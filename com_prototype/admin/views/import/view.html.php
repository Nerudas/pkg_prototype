<?php
/**
 * @package    Prototype Component
 * @version    1.4.3
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Language\Text;

class PrototypeViewImport extends HtmlView
{

	/**
	 * Display the view
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed A string if successful, otherwise an Error object.
	 *
	 * @throws Exception
	 *
	 * @since  1.4.0
	 */
	public function display($tpl = null)
	{
		PrototypeHelper::addSubmenu('import');


		$this->sidebar = JHtmlSidebar::render();

		JToolBarHelper::title(Text::_('COM_PROTOTYPE') . ': ' . Text::_('COM_PROTOTYPE_IMPORT'), 'clock');
		$this->document->setTitle(TEXT::_('COM_PROTOTYPE_IMPORT'));

		return parent::display($tpl);
	}
}