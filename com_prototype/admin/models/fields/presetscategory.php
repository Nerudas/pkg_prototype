<?php
/**
 * @package    Prototype Component
 * @version    1.4.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

FormHelper::loadFieldClass('list');

class JFormFieldPresetsCategory extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'presetsCategory';

	/**
	 * Preset
	 *
	 * @var    string
	 *
	 * @since  1.2.0
	 */
	protected $preset = null;


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
			$this->preset = (!empty($this->element['preset'])) ? (string) $this->element['preset'] : null;
		}
		if ($this->preset == 'icon')
		{
			$this->layout = 'components.com_prototype.admin.preseticon';
		}

		return $return;
	}

	protected function getInput()
	{
		if ($this->preset == 'demo')
		{
			return '<div class="center span12"><a data-preset-demo="' . $this->name . '" class="icon-eye" href="javascript:void(0);"></a></div>';
		}

		if ($this->preset == 'icon')
		{
			return $this->getRenderer($this->layout)->render($this->getLayoutData());
		}

		return parent::getInput();
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
		if ($this->preset == 'demo' || $this->preset == 'icon')
		{
			return array();
		}

		$registry = new Registry(ComponentHelper::getParams('com_prototype')->get('presets', array()));
		$presets  = $registry->toArray();
		$options  = parent::getOptions();

		if (!empty($presets[$this->preset]))
		{
			foreach ($presets[$this->preset] as $preset)
			{
				$option           = new stdClass();
				$option->value    = $preset['value'];
				$option->text     = $preset['title'];
				$option->selected = ($option->value == $this->value);

				$options[] = $option;
			}
		}

		return $options;
	}
}