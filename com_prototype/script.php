<?php
/**
 * @package    Prototype Package
 * @version    1.3.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class com_PrototypeInstallerScript
{
	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	function postflight()
	{
		$path = '/components/com_prototype';

		$this->fixTables($path);
		$this->tagsIntegration();
		$this->createImageFolder();
		$this->moveLayouts($path);
		$this->createRootCategory();

		return true;
	}

	/**
	 * Create or image folders
	 *
	 * @since 1.0.0
	 */
	protected function createImageFolder()
	{
		$folders = array(
			JPATH_ROOT . '/images/prototype',
			JPATH_ROOT . '/images/prototype/categories',
			JPATH_ROOT . '/images/prototype/items',
			JPATH_ROOT . '/images/prototype/placemarks',
		);
		foreach ($folders as $folder)
		{
			if (!JFolder::exists($folder))
			{
				JFolder::create($folder);
				JFile::write($folder . '/index.html', '<!DOCTYPE html><title></title>');
			}
		}
	}

	/**
	 * Create root category
	 *
	 * @since  1.0.0
	 */
	protected function createRootCategory()
	{
		$db = Factory::getDbo();

		// Category
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__prototype_categories'))
			->where($db->quoteName('id') . ' = ' . $db->quote(1));
		$db->setQuery($query);
		$current_id = $db->loadResult();

		$root            = new stdClass();
		$root->id        = 1;
		$root->parent_id = 0;
		$root->lft       = 0;
		$root->rgt       = 1;
		$root->level     = 0;
		$root->path      = '';
		$root->alias     = 'root';
		$root->access    = 1;
		$root->state     = 1;

		(!empty($current_id)) ? $db->updateObject('#__prototype_categories', $root, 'id')
			: $db->insertObject('#__prototype_categories', $root);
	}


	/**
	 * Create or update tags integration
	 *
	 * @since 1.0.0
	 */
	protected function tagsIntegration()
	{
		$db = Factory::getDbo();

		// Category
		$query = $db->getQuery(true)
			->select('type_id')
			->from($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_prototype.category'));
		$db->setQuery($query);
		$current_id = $db->loadResult();

		$category                                               = new stdClass();
		$category->type_id                                      = (!empty($current_id)) ? $current_id : '';
		$category->type_title                                   = 'Prototype category';
		$category->type_alias                                   = 'com_prototype.category';
		$category->table                                        = new stdClass();
		$category->table->special                               = new stdClass();
		$category->table->special->dbtable                      = '#__prototype_categories';
		$category->table->special->key                          = 'id';
		$category->table->special->type                         = 'Categories';
		$category->table->special->prefix                       = 'PrototypeTable';
		$category->table->special->config                       = 'array()';
		$category->table->common                                = new stdClass();
		$category->table->common->dbtable                       = '#__ucm_content';
		$category->table->common->key                           = 'ucm_id';
		$category->table->common->type                          = 'Corecontent';
		$category->table->common->prefix                        = 'JTable';
		$category->table->common->config                        = 'array()';
		$category->table                                        = json_encode($category->table);
		$category->rules                                        = '';
		$category->field_mappings                               = new stdClass();
		$category->field_mappings->common                       = new stdClass();
		$category->field_mappings->common->core_content_item_id = 'id';
		$category->field_mappings->common->core_title           = 'title';
		$category->field_mappings->common->core_state           = 'state';
		$category->field_mappings->common->core_alias           = 'alias';
		$category->field_mappings->common->core_created_time    = 'null';
		$category->field_mappings->common->core_modified_time   = 'null';
		$category->field_mappings->common->core_body            = 'null';
		$category->field_mappings->common->core_hits            = 'null';
		$category->field_mappings->common->core_publish_up      = 'null';
		$category->field_mappings->common->core_publish_down    = 'null';
		$category->field_mappings->common->core_access          = 'access';
		$category->field_mappings->common->core_params          = 'attribs';
		$category->field_mappings->common->core_featured        = 'null';
		$category->field_mappings->common->core_metadata        = 'metadata';
		$category->field_mappings->common->core_language        = 'null';
		$category->field_mappings->common->core_images          = 'null';
		$category->field_mappings->common->core_urls            = 'null';
		$category->field_mappings->common->core_version         = 'null';
		$category->field_mappings->common->core_ordering        = 'lft';
		$category->field_mappings->common->core_metakey         = 'metakey';
		$category->field_mappings->common->core_metadesc        = 'metadesc';
		$category->field_mappings->common->core_catid           = 'null';
		$category->field_mappings->common->core_xreference      = 'null';
		$category->field_mappings->common->asset_id             = 'null';
		$category->field_mappings->special                      = new stdClass();
		$category->field_mappings                               = json_encode($category->field_mappings);
		$category->router                                       = 'PrototypeHelperRoute::getListRoute';
		$category->content_history_options                      = '';

		(!empty($current_id)) ? $db->updateObject('#__content_types', $category, 'type_id')
			: $db->insertObject('#__content_types', $category);

		// Item
		$query = $db->getQuery(true)
			->select('type_id')
			->from($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_prototype.item'));
		$db->setQuery($query);
		$current_id = $db->loadResult();

		$item                                               = new stdClass();
		$item->type_id                                      = (!empty($current_id)) ? $current_id : '';
		$item->type_title                                   = 'Prototype item';
		$item->type_alias                                   = 'com_prototype.item';
		$item->table                                        = new stdClass();
		$item->table->special                               = new stdClass();
		$item->table->special->dbtable                      = '#__prototype_items';
		$item->table->special->key                          = 'id';
		$item->table->special->type                         = 'Items';
		$item->table->special->prefix                       = 'PrototypeTable';
		$item->table->special->config                       = 'array()';
		$item->table->common                                = new stdClass();
		$item->table->common->dbtable                       = '#__ucm_content';
		$item->table->common->key                           = 'ucm_id';
		$item->table->common->type                          = 'Corecontent';
		$item->table->common->prefix                        = 'JTable';
		$item->table->common->config                        = 'array()';
		$item->table                                        = json_encode($item->table);
		$item->rules                                        = '';
		$item->field_mappings                               = new stdClass();
		$item->field_mappings->common                       = new stdClass();
		$item->field_mappings->common->core_content_item_id = 'id';
		$item->field_mappings->common->core_title           = 'title';
		$item->field_mappings->common->core_state           = 'state';
		$item->field_mappings->common->core_alias           = 'null';
		$item->field_mappings->common->core_created_time    = 'null';
		$item->field_mappings->common->core_modified_time   = 'null';
		$item->field_mappings->common->core_body            = 'text';
		$item->field_mappings->common->core_hits            = 'hits';
		$item->field_mappings->common->core_publish_up      = 'null';
		$item->field_mappings->common->core_publish_down    = 'null';
		$item->field_mappings->common->core_access          = 'access';
		$item->field_mappings->common->core_params          = 'null';
		$item->field_mappings->common->core_featured        = 'null';
		$item->field_mappings->common->core_metadata        = 'null';
		$item->field_mappings->common->core_language        = 'null';
		$item->field_mappings->common->core_images          = 'images';
		$item->field_mappings->common->core_urls            = 'null';
		$item->field_mappings->common->core_version         = 'null';
		$item->field_mappings->common->core_ordering        = 'created';
		$item->field_mappings->common->core_metakey         = 'null';
		$item->field_mappings->common->core_metadesc        = 'null';
		$item->field_mappings->common->core_catid           = 'null';
		$item->field_mappings->common->core_xreference      = 'null';
		$item->field_mappings->common->asset_id             = 'null';
		$item->field_mappings->special                      = new stdClass();
		$item->field_mappings->special->region              = 'region';
		$item->field_mappings                               = json_encode($item->field_mappings);
		$item->router                                       = 'PrototypeHelperRoute::getItemRoute';
		$item->content_history_options                      = '';

		(!empty($current_id)) ? $db->updateObject('#__content_types', $item, 'type_id')
			: $db->insertObject('#__content_types', $item);
	}

	/**
	 * Move layouts folder
	 *
	 * @param string $path path to files
	 *
	 * @since 1.0.0
	 */
	protected function moveLayouts($path)
	{
		$component = JPATH_ADMINISTRATOR . $path . '/layouts';
		$layouts   = JPATH_ROOT . '/layouts' . $path;
		if (!JFolder::exists(JPATH_ROOT . '/layouts/components'))
		{
			JFolder::create(JPATH_ROOT . '/layouts/components');
		}
		if (JFolder::exists($layouts))
		{
			JFolder::delete($layouts);
		}
		JFolder::move($component, $layouts);
	}

	/**
	 *
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance $adapter The object responsible for running this script
	 *
	 * @since 1.0.0
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		$db = Factory::getDbo();

		// Remove content_type
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_prototype.category'));
		$db->setQuery($query)->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__content_types'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_prototype.item'));
		$db->setQuery($query)->execute();

		// Remove tag_map
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_prototype.category'));
		$db->setQuery($query)->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_prototype.item'));
		$db->setQuery($query)->execute();

		// Remove ucm_content
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ucm_content'))
			->where($db->quoteName('core_type_alias') . ' = ' . $db->quote('com_prototype.category'));
		$db->setQuery($query)->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ucm_content'))
			->where($db->quoteName('core_type_alias') . ' = ' . $db->quote('com_prototype.item'));
		$db->setQuery($query)->execute();

		// Remove images
		JFolder::delete(JPATH_ROOT . '/images/com_prototype');

		// Remove layouts
		JFolder::delete(JPATH_ROOT . '/layouts/components/com_prototype');
	}

	/**
	 * Method to fix tables
	 *
	 * @param string $path path to component directory
	 *
	 * @since 1.0.0
	 */
	protected function fixTables($path)
	{
		$file = JPATH_ADMINISTRATOR . $path . '/sql/install.mysql.utf8.sql';
		if (!empty($file))
		{
			$sql = JFile::read($file);

			if (!empty($sql))
			{
				$db      = Factory::getDbo();
				$queries = $db->splitSql($sql);
				foreach ($queries as $query)
				{
					$db->setQuery($db->convertUtf8mb4QueryToUtf8($query));
					try
					{
						$db->execute();
					}
					catch (JDataBaseExceptionExecuting $e)
					{
						JLog::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()),
							JLog::WARNING, 'jerror');
					}
				}
			}
		}
	}

	/**
	 * Change database structure && delete association
	 *
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 *
	 * @since  1.0.0
	 */
	public function update($parent)
	{
		$db = Factory::getDbo();

		$db->setQuery("DROP TABLE IF EXISTS  #__prototype_placemarks")->execute();
		if (JFolder::exists(JPATH_ROOT . '/images/prototype/placemarks'))
		{
			JFolder::delete(JPATH_ROOT . '/images/prototype/placemarks');
		}

		$table   = '#__prototype_categories';
		$columns = $db->getTableColumns($table);
		if (isset($columns['fields']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP fields")->query();
		}
		if (isset($columns['filters']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP filters")->query();
		}
		if (isset($columns['placemark_id']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP placemark_id")->query();
		}
		if (isset($columns['balloon_layout']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP balloon_layout")->query();
		}
		if (isset($columns['listitem_layout']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP listitem_layout")->query();
		}
		if (!isset($columns['presets']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `presets` LONGTEXT NOT NULL DEFAULT '' AFTER `attribs`")
				->query();
		}

		// Items
		$table   = '#__prototype_items';
		$columns = $db->getTableColumns($table);
		if (!isset($columns['text']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `text` TEXT NOT NULL DEFAULT '' AFTER `title`")
				->query();
		}
		if (!isset($columns['location']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `location` TEXT NOT NULL DEFAULT '' AFTER `text`")
				->query();
		}
		if (!isset($columns['price']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `price` BIGINT NOT NULL DEFAULT '0' AFTER `location`")
				->query();
		}
		if (!isset($columns['preset_price']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `preset_price` TEXT NOT NULL DEFAULT '' AFTER `price`")
				->query();
		}
		if (!isset($columns['preset_delivery']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `preset_delivery` TEXT NOT NULL DEFAULT '' AFTER `preset_price`")
				->query();
		}
		if (!isset($columns['preset_object']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `preset_object` TEXT NOT NULL DEFAULT '' AFTER `preset_delivery`")
				->query();
		}
		if (!isset($columns['external_link']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `external_link` TEXT NOT NULL DEFAULT '' AFTER `preset_object`")
				->query();
		}
		if (!isset($columns['payment']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `payment` TINYINT(3) NOT NULL DEFAULT '0' AFTER `created_by`")
				->query();
		}
		if (!isset($columns['payment_down']))
		{
			$db->setQuery("ALTER TABLE " . $table . " ADD `payment_down`  DATETIME  NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `payment_number`")
				->query();
		}
		if (isset($columns['extra']))
		{
			$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName($table));
			$db->setQuery($query);
			$items = $db->loadObjectList('id');

			foreach ($items as &$item)
			{
				$extra = new Registry($item->extra);

				$item->text            = $extra->get('why_you', $extra->get('comment'));
				$item->external_link   = $extra->get('discussion_link');
				$item->price           =
					$extra->get('price_m3',
						$extra->get('price_t',
							$extra->get('price_h',
								$extra->get('price_s',
									$extra->get('price_o')
								)
							)
						)
					);
				$item->preset_price    = 'null';
				$item->preset_delivery = 'null';
				$item->preset_object   = 'null';

				$db->updateObject($table, $item, array('id'));
			}
		}

		if (isset($columns['html']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP html")->query();
		}
		if (isset($columns['publish_down']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP publish_down")->query();
		}
		if (isset($columns['placemark_id']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP placemark_id")->query();
		}
		if (isset($columns['balloon_layout']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP balloon_layout")->query();
		}
		if (isset($columns['listitem_layout']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP listitem_layout")->query();
		}
		if (isset($columns['extra']))
		{
			$db->setQuery("ALTER TABLE " . $table . " DROP extra")->query();
		}
	}
}