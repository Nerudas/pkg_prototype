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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;

class JFormFieldPresetsItem extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'presetsItem';

	/**
	 * Preset
	 *
	 * @var    string
	 *
	 * @since  1.2.0
	 */
	protected $catid = null;

	/**
	 * Preset
	 *
	 * @var    array
	 *
	 * @since  1.2.0
	 */
	protected $_presets = array();

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  1.2.0
	 */
	protected $layout = 'components.com_prototype.admin.presetsitem';

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
			$this->catid = $this->form->getData()->get('catid', 0);
		}

		return $return;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  1.2.0
	 */
	protected function getLayoutData()
	{
		$data            = parent::getLayoutData();
		$data['presets'] = $this->getPresets();

		return $data;
	}

	/**
	 * Method to get presets
	 *
	 * @param int $catid
	 *
	 * @return array
	 *
	 * @since  1.3.0
	 */
	protected function getPresets($catid = null)
	{
		$catid = (empty($catid)) ? $this->catid : $catid;
		if (empty($catid))
		{
			return array();
		}
		if (!isset($this->_presets[$catid]))
		{
			$presets = array();
			$db      = Factory::getDbo();
			$query   = $db->getQuery(true)
				->select('presets')
				->from('#__prototype_categories')
				->where('id = ' . (int) $this->catid);
			$db->setQuery($query);
			$registry = new Registry($db->loadResult());
			$rows     = $registry->toArray();

			if (!empty($registry))
			{
				$registry = new Registry(ComponentHelper::getParams('com_prototype')->get('presets', array()));
				$configs  = $registry->toArray();

				$configPresets = array();
				foreach ($configs as $key => $config)
				{
					if (!isset($configPresets[$key]))
					{
						$configPresets[$key] = array();
					}
					foreach ($config as $conf)
					{
						$configPresets[$key][$conf['value']] = new Registry($conf);
					}
				}

				foreach ($rows as $preset)
				{
					$preset['price_title']    = (!empty($configPresets['price'][$preset['price']])) ?
						$configPresets['price'][$preset['price']]->get('title', $preset['price']) : $preset['price'];
					$preset['delivery_title'] = (!empty($configPresets['delivery'][$preset['delivery']])) ?
						$configPresets['delivery'][$preset['delivery']]->get('title', $preset['delivery']) : $preset['delivery'];
					$preset['object_title']   = (!empty($configPresets['object'][$preset['object']])) ?
						$configPresets['object'][$preset['object']]->get('title', $preset['object']) : $preset['object'];
					$presets[$preset['key']]  = $preset;
				}
			}

			$this->_presets[$catid] = $presets;
		}

		return $this->_presets[$catid];
	}
}