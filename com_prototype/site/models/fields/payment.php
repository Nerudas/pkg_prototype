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

use Joomla\CMS\Form\FormHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;

FormHelper::loadFieldClass('radio');

class JFormFieldPayment extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'paymaent';

	protected $layout = 'components.com_prototype.form.payment';

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
			$payment['checked'] = ($payment['value'] == $this->value);
			$options[]          = $payment;
		}

		return $options;
	}
}

