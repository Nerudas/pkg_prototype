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

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Session\Session;

class PrototypeControllerItems extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_PROTOTYPE_ITEMS';

	/**
	 *
	 * Proxy for getModel.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config The array of possible config values. Optional.
	 *
	 * @return  JModelLegacy
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Item', $prefix = 'PrototypeModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to clone an existing item.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$pks = $this->input->post->get('cid', array(), 'array');
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_PROTOTYPE_ERROR_NO_ITEMS_SELECTED'));
			}

			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::plural('COM_PROTOTYPE_ITEMS_N_ITEMS_DUPLICATED', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_prototype&view=items');
	}

	/**
	 *  Method to prolong items to 3 days.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function prolong_3d()
	{
		$this->prolong('3 day');
	}

	/**
	 * Method to prolong items to 1 week.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function prolong_1w()
	{
		$this->prolong('1 week');
	}

	/**
	 * Method to prolong items to 1 month.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function prolong_1m()
	{
		$this->prolong('1 month');
	}

	/**
	 * Method to prolong items.
	 *
	 * @param string $plus date plus publish_down
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function prolong($plus = '')
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$pks = $this->input->post->get('cid', array(), 'array');
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_PROTOTYPE_ERROR_NO_ITEMS_SELECTED'));
			}

			$model = $this->getModel();
			$model->prolong($pks, $plus);
			$this->setMessage(Text::plural('COM_PROTOTYPE_ITEMS_N_ITEMS_PROLONGED', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_prototype&view=items');
	}

	/**
	 * Method to prolong items.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function setPaymentNumber()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$pks = $this->input->post->get('cid', array(), 'array');
		$pks = ArrayHelper::toInteger($pks);

		$filter = $this->input->post->get('filter', array(), 'array');

		$payment_number = (!empty($filter['payment_number'])) ? $filter['payment_number'] : '';

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_PROTOTYPE_ERROR_NO_ITEMS_SELECTED'));
			}

			if (empty($payment_number))
			{
				throw new Exception(Text::_('COM_PROTOTYPE_ERROR_EMPTY_PAYMENT_NUMBER'));
			}

			$model = $this->getModel();
			$model->setPaymentNumber($pks, $payment_number);
			$this->setMessage(Text::plural('COM_PROTOTYPE_ITEMS_N_ITEMS_SET_PAYMENT_NUMBER', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_prototype&view=items');
	}
}