<?php
/**
 * @package    Prototype Component
 * @version    1.4.2
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;

FormHelper::loadFieldClass('list');

class JFormFieldPayment extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'paymaent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{

		$registry = new Registry(ComponentHelper::getParams('com_prototype')->get('payment', array()));
		$payments = $registry->toArray();

		$options = parent::getOptions();
		foreach ($payments as $i => $payment)
		{
			$option        = new stdClass();
			$option->value = $payment['value'];
			$option->text  = $payment['title'];
			$options[]     = $option;
		}

		return $options;
	}
}