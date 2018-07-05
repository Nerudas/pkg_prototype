<?php
/**
 * @package    Prototype Component
 * @version    1.0.6
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

FormHelper::loadFieldClass('list');

class JFormFieldPlusPublishDown extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.3
	 */
	protected $type = 'PlusPublishDown';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.0.3
	 */
	protected function getInput()
	{
		$this->class = 'input-small';

		$name  = $this->name;
		$id    = $this->id;
		$value = (!empty($this->value) && is_array($this->value)) ? $this->value : array('number' => '', 'variable' => '');

		$this->name = $name . '[variable]';
		$this->id   = $id . '_variable';
		$select     = parent::getInput();

		$data            = $this->getLayoutData();
		$data['name']    = $name . '[number]';
		$data['id']      = $id . '_number';
		$data['options'] = array();
		$data['dirname'] = '';
		$data['value']   = $value['number'];


		$text = LayoutHelper::render('joomla.form.field.text', $data);


		return $text . $select;
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
		$options = array();

		$value = (!empty($this->value) && is_array($this->value)) ? $this->value : array('number' => '', 'variable' => '');
		foreach (array('day', 'week', 'month', 'year') as $date)
		{
			$option           = new stdClass();
			$option->value    = $date;
			$option->text     = Text::_('COM_PROTOTYPE_FILED_PLUS_PUBLISH_DOWN_' . $date);
			$option->selected = ($value['variable'] == $date);

			$options[] = $option;
		}

		return $options;

	}
}