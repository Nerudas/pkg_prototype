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

use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

FormHelper::loadFieldClass('list');

class JFormFieldLayout extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'layout';

	/**
	 * Layouts folder
	 *
	 * @var   string
	 *
	 * @since  1.0.0
	 */
	protected $folder;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 *
	 * @since   1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if ($return = parent::setup($element, $value, $group))
		{
			$this->folder = (!empty($this->element['folder'])) ? (string) $this->element['folder'] : '';
		}

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		if (!empty($this->folder))
		{
			$path = '/layouts/components/com_prototype/' . $this->folder;

			$files = array();

			if (JFolder::exists(JPATH_ROOT . $path))
			{
				$files = array_merge($files, JFolder::files(JPATH_ROOT . $path, '.php', false));
			}

			$templates = JFolder::folders(JPATH_ROOT . '/templates');
			foreach ($templates as $template)
			{
				if (JFolder::exists(JPATH_ROOT . '/templates/' . $template . '/html/' . $path))
				{
					$files = array_merge($files, JFolder::files(JPATH_ROOT . '/templates/' . $template . '/html/' . $path,
						'.php', false));
				}
			}

			$files = array_unique($files);

			foreach ($files as $file)
			{
				$name          = str_replace('.php', '', $file);
				$option        = new stdClass();
				$option->value = $name;
				$option->text  = $name;
				$options[]     = $option;
			}
		}

		return $options;
	}
}