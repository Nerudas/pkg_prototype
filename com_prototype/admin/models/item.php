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

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class PrototypeModelItem extends AdminModel
{
	/**
	 * The active category
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $_category = array();

	/**
	 * Imagefolder helper helper
	 *
	 * @var    new imageFolderHelper
	 *
	 * @since  1.0.0
	 */
	protected $imageFolderHelper = null;

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     AdminModel
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		JLoader::register('imageFolderHelper', JPATH_PLUGINS . '/fieldtypes/ajaximage/helpers/imagefolder.php');
		$this->imageFolderHelper = new imageFolderHelper('images/prototype/items');

		parent::__construct($config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the attribs field to an array.
			$registry      = new Registry($item->attribs);
			$item->attribs = $registry->toArray();

			// Convert the map field to an array.
			$registry  = new Registry($item->map);
			$item->map = $registry->toArray();

			// Get Tags
			$item->tags = new TagsHelper;
			$item->tags->getItemTags($item->id, 'com_prototype.item');

			// Convert the extra field to an array.
			$registry    = new Registry($item->extra);
			$item->extra = $registry->toArray();

			$item->published = $item->state;
		}

		return $item;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string $type   The table type to instantiate
	 * @param   string $prefix A prefix for the table class name. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 * @since  1.0.0
	 */
	public function getTable($type = 'Items', $prefix = 'PrototypeTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since  1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app  = Factory::getApplication();
		$form = $this->loadForm('com_prototype.item', 'item', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		/*
		 * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		 * The back end uses id so we use that the rest of the time and set it to 0 by default.
		 */
		$id   = ($this->getState('item.id')) ? $this->getState('item.id') : $app->input->get('id', 0);
		$user = Factory::getUser();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_prototype.item.' . (int) $id)))
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an item you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		// Set update images link
		$form->setFieldAttribute('images', 'saveurl',
			Uri::base(true) . '/index.php?option=com_prototype&task=item.updateImages&field=images&id=' . $id);

		// Set Placemark link
		$form->setFieldAttribute('map', 'placemarkurl',
			Uri::base(true) . '/index.php?option=com_prototype&task=item.getPlacemark&id=' . $id);


		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since  1.0.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_prototype.edit.item.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		$this->preprocessData('com_prototype.item', $data);

		return $data;
	}


	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.0.0
	 */
	public function save($data)
	{
		$app    = Factory::getApplication();
		$pk     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$filter = InputFilter::getInstance();
		$table  = $this->getTable();
		$db     = Factory::getDbo();
		$isNew  = true;

		$catid             = $data['catid'];
		$category          = $this->getCategory($catid);
		$category->attribs = new Registry($category->attribs);

		// Load the row if saving an existing type.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		if ($app->isSite() && $isNew)
		{
			$data['state'] = 0;
			if ($category->front_created == 2)
			{
				$data['state'] = 1;

				$auto_publish_down = $category->attribs->get('item_publish_down', false);
				if ($auto_publish_down && !empty($auto_publish_down->number))
				{
					$data['publish_down'] = Factory::getDate('+ ' . $auto_publish_down->number . ' ' .
						$auto_publish_down->variable)->toSql();
				}
			}
			elseif ($category->front_created == 0)
			{
				return false;
			}
		}

		if (!empty($data['plus_publish_down']) && is_array($data['plus_publish_down'])
			&& !empty($data['plus_publish_down']['number']))
		{
			$data['publish_down'] = Factory::getDate('+ ' . $data['plus_publish_down']['number'] . ' ' .
				$data['plus_publish_down']['variable'])->toSql();
		}

		if (empty($data['created']))
		{
			$data['created'] = Factory::getDate()->toSql();
		}
		if (empty($data['region']))
		{
			$data['region'] = $app->input->cookie->get('region', '*');
		}

		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
		}


		if (isset($data['map']) && is_array($data['map']))
		{
			if (!empty($data['map']['placemark']) && !empty($data['map']['placemark']['coordinates']))
			{
				$data['latitude']  = $data['map']['placemark']['latitude'];
				$data['longitude'] = $data['map']['placemark']['longitude'];
			}
			$registry    = new Registry($data['map']);
			$data['map'] = (string) $registry;
		}
		if (!isset($data['latitude']) && !isset($data['longitude']))
		{
			$data['latitude']  = '';
			$data['longitude'] = '';
		}

		if (isset($data['attribs']) && is_array($data['attribs']))
		{
			$registry        = new Registry($data['attribs']);
			$data['attribs'] = (string) $registry;
		}

		if (isset($data['metadata']) && is_array($data['metadata']))
		{
			$registry         = new Registry($data['metadata']);
			$data['metadata'] = (string) $registry;
		}

		if (empty($data['created_by']))
		{
			$data['created_by'] = Factory::getUser()->id;
		}


		if (!empty($data['extra']) && $catid > 1 && $category)
		{
			$categoryFields = (!empty($category->fields)) ? $category->fields : array();
			foreach ($data['extra'] as $name => $value)
			{
				if (empty($categoryFields[$name]))
				{
					unset($data['extra'][$name]);
				}
			}
		}
		else
		{
			$data['extra'] = array();
		}

		if (isset($data['extra']) && is_array($data['extra']))
		{
			$registry      = new Registry($data['extra']);
			$data['extra'] = (string) $registry;
		}

		// Get tags search
		$data['tags'] = (!empty($category) && !empty($category->items_tags)) ? explode(',', $category->items_tags) : array();
		if (!empty($data['tags']))
		{
			$query = $db->getQuery(true)
				->select(array('id', 'title'))
				->from('#__tags')
				->where('id IN (' . implode(',', $data['tags']) . ')');
			$db->setQuery($query);
			$tags = $db->loadObjectList();

			$tags_search = array();
			$tags_map    = array();
			foreach ($tags as $tag)
			{
				$tags_search[$tag->id] = $tag->title;
				$tags_map[$tag->id]    = '[' . $tag->id . ']';
			}

			$data['tags_search'] = implode(', ', $tags_search);
			$data['tags_map']    = implode('', $tags_map);
		}
		else
		{
			$data['tags_search'] = '';
			$data['tags_map']    = '';
		}

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($app->input->getInt('id'));

			// Change title
			if ($data['title'] == $origTable->title)
			{
				$data['title'] = $data['title'] . ' ' . Text::_('JGLOBAL_COPY');
			}

			$data['state']     = 0;
			$data['published'] = 0;

			// Copy images
			$data['imagefolder'] = $this->imageFolderHelper->createTemporaryFolder();
			if (!empty($data['images']))
			{
				foreach ($data['images'] as &$image)
				{
					$old          = JPATH_ROOT . '/' . $image['src'];
					$image['src'] = $data['imagefolder'] . '/' . $image['file'];
					$new          = JPATH_ROOT . '/' . $image['src'];
					JFile::copy($old, $new);
				}
			}
		}

		if (parent::save($data))
		{
			$id = $this->getState($this->getName() . '.id');

			// Save images
			$data['imagefolder'] = (!empty($data['imagefolder'])) ? $data['imagefolder'] :
				$this->imageFolderHelper->getItemImageFolder($id);

			if ($isNew)
			{
				$data['images'] = (isset($data['images'])) ? $data['images'] : array();
			}

			if (isset($data['images']))
			{
				$this->imageFolderHelper->saveItemImages($id, $data['imagefolder'], '#__prototype_items', 'images', $data['images']);
			}

			return $id;
		}

		return false;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array &$pks An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since  1.0.0
	 */
	public function delete(&$pks)
	{
		if (parent::delete($pks))
		{
			// Delete images
			foreach ($pks as $pk)
			{
				$this->imageFolderHelper->deleteItemImageFolder($pk);
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getCategory($pk = 1)
	{
		$this->_category[1] = false;
		if (!isset($this->_category[$pk]))
		{
			try
			{
				$model = parent::getInstance('Category', $prefix = 'PrototypeModel', $config = array('ignore_request' => true));

				$this->_category[$pk] = $model->getItem($pk);
			}
			catch (Exception $e)
			{

				$this->setError($e);
				$this->_category[$pk] = false;
			}
		}

		return $this->_category[$pk];
	}

	/**
	 * Method to duplicate one or more records.
	 *
	 * @param   array &$pks An array of primary key IDs.
	 *
	 * @return  boolean|JException  Boolean true on success, JException instance on error
	 *
	 * @since  1.0.0
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		// Access checks.
		if (!Factory::getUser()->authorise('core.create', 'com_prototype'))
		{
			throw new Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		foreach ($pks as $pk)
		{
			if ($item = $this->getItem($pk))
			{
				unset($item->id);
				$item->title       = $item->title . ' ' . Text::_('JGLOBAL_COPY');
				$item->published   = $item->state = 0;
				$item->imagefolder = $this->imageFolderHelper->createTemporaryFolder();
				if (!empty($item->images))
				{
					$registry     = new Registry($item->images);
					$item->images = $registry->toArray();
					foreach ($item->images as &$image)
					{
						$old          = JPATH_ROOT . '/' . $image['src'];
						$image['src'] = $item->imagefolder . '/' . $image['file'];
						$new          = JPATH_ROOT . '/' . $image['src'];
						JFile::copy($old, $new);
					}
				}

				$this->save(ArrayHelper::fromObject($item));
			}
		}

		$this->cleanCache();

		return true;
	}

	/**
	 * Method to prolong items to date
	 *
	 * @param   array &$pks An array of primary key IDs.
	 *
	 * @param string  $plus date plus publish_down
	 *
	 * @return  boolean|JException  Boolean true on success, JException instance on error
	 *
	 * @throws Exception
	 * @since  1.0.0
	 */
	public function prolong(&$pks, $plus = '')
	{
		// Access checks.
		if (!Factory::getUser()->authorise('core.edit', 'com_prototype'))
		{
			throw new Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		if (!empty($pks) && !empty($plus))
		{
			$table = '#__prototype_items';
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(array('id', 'publish_down'))
				->from($table)
				->where('id IN(' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$items = $db->loadObjectList();

			foreach ($items as $item)
			{
				$publish_down       = ($item->publish_down > 0) ? $item->publish_down : Factory::getDate()->toSql();
				$item->publish_down = Factory::getDate($publish_down . ' +' . $plus)->toSql();

				$db->updateObject($table, $item, array('id'));
			}

		}

		$this->cleanCache();

		return true;
	}
}