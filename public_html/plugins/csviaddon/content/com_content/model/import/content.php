<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaContent
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace content\com_content\model\import;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Content import.
 *
 * @package     CSVI
 * @subpackage  JoomlaContent
 * @since       6.0
 */
class Content extends \RantaiImportEngine
{
	/**
	 * Content table.
	 *
	 * @var    \ContentTableContent
	 * @since  6.0
	 */
	private $content = null;

	/**
	 * The Joomla content helper
	 *
	 * @var    \Com_ContentHelperCom_Content
	 * @since  6.0
	 */
	protected $helper = null;

	/**
	 * List of available custom fields
	 *
	 * @var    array
	 * @since  7.2.0
	 */
	private $customFields = '';

	/**
	 * Run this before we start.
	 *
	 * @return  void.
	 *
	 * @throws  \Exception
	 *
	 * @since   7.2.0
	 */
	public function onBeforeStart()
	{
		// Load the tables that will contain the data
		$this->loadCustomFields();
	}

	/**
	 * Start the product import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   6.0
	 */
	public function getStart()
	{
		// Process data
		foreach ($this->fields->getData() as $fields)
		{
			foreach ($fields as $name => $details)
			{
				$value = $details->value;

				switch ($name)
				{
					case 'category_path':
						$this->setState('catid', $this->helper->getCategoryId($value));
						$this->setState($name, $value);
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$requiredFields = true;

		// Bind the data and set the title
		$this->setState('title', $this->getState('title', $this->getState('alias', false)));
		$this->content->bind(ArrayHelper::fromObject($this->state));

		if ($this->content->check())
		{
			$this->setState('alias', $this->content->get('alias'));

			// There must be an id or alias and catid or category_path
			if ($this->getState('id', false) || $this->getState('title', false)
				|| ($this->getState('alias', false) && ($this->getState('catid', false) || $this->getState('category_path', false))))
			{
				$this->loaded = true;

				if (!$this->getState('id', false))
				{
					$this->setState('id', $this->helper->getContentId($this->getState('alias', false), $this->getState('catid', false)));
				}

				// Load the current content data
				if ($this->content->load($this->getState('id', 0)))
				{
					if (!$this->template->get('overwrite_existing_data'))
					{
						$this->log->add('Article ' . $this->getState('alias') . 'not updated because the option overwrite existing data is set to No');
						$this->loaded = false;
					}
				}
			}
			else
			{
				$requiredFields = false;
			}
		}
		else
		{
			$requiredFields = false;
		}

		if (!$requiredFields)
		{
			$field        = array('id or alias or title', 'catid or category_path');
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_NO_REQUIRED_FIELD_FOUND', implode(',', $field)));
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no product SKU or product ID can be found.
	 *
	 * @since   6.0
	 */
	public function getProcessRecord()
	{
		if ($this->loaded)
		{
			if (!$this->getState('id', false) && $this->template->get('ignore_non_exist'))
			{
				// Do nothing for new products when user chooses to ignore new products
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('alias', '')));
			}
			else
			{
				// Set the attributes
				$this->setAttributes();

				// Set the images
				$this->setImages();

				// Set the urls
				$this->setUrls();

				// Check for meta data
				$this->setMetadata();

				// Data must be in an array
				$data = ArrayHelper::fromObject($this->state);

				// Add a creating date if there is no product_id
				if (!$this->getState('id', false))
				{
					if (!$this->getState('created_by'))
					{
						$this->content->created_by = $this->userId;
					}

					if (!$this->getState('created'))
					{
						$this->content->created = $this->date->toSql();
					}
				}
				else
				{
					if (!$this->getState('modified', false))
					{
						$this->content->modified = $this->date->toSql();
					}

					if (!$this->getState('modified_by'))
					{
						$this->content->modified_by = $this->userId;
					}
				}

				$this->content->bind($data);

				// Check if we use a given order id
				if ($this->template->get('keepid'))
				{
					$this->content->checkId();
				}

				try
				{
					$this->content->store();

					if (!$this->getState('id', 0))
					{
						$this->log->addStats('Added', \JText::_('COM_CSVI_JOOMLA_CONTENT_ADDED'));
					}
					else
					{
						$this->log->addStats('Updated', \JText::_('COM_CSVI_JOOMLA_CONTENT_UPDATED'));
					}

					$this->processCustomFields($this->content->id);
				}
				catch (Exception $e)
				{
					$this->log->add('Cannot add Joomla content. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', $e->getMessage());

					return false;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Get a list of custom fields that can be used as available field.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 *
	 * @throws  \Exception
	 */
	private function loadCustomFields()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('name'))
			->from($this->db->quoteName('#__fields'))
			->where($this->db->quoteName('state') . ' = 1')
			->where($this->db->quoteName('context') . ' = ' . $this->db->quote('com_content.article'));
		$this->db->setQuery($query);
		$this->customFields = $this->db->loadObjectList();

		$this->log->add('Load the Joomla custom fields for articles');
	}

	/**
	 * Update custom fields data.
	 *
	 * @param   int  $id  Id of the article
	 *
	 * @return  bool Returns true if all is OK | Returns false otherwise
	 *
	 * @since   7.2.0
	 */
	private function processCustomFields($id)
	{
		if (count($this->customFields) === 0)
		{
			$this->log->add('No custom fields found', false);

			return false;
		}

		foreach ($this->customFields as $field)
		{
			$fieldName = $field->name;

			if ($this->getState($fieldName, ''))
			{
				$query = $this->db->getQuery(true);
				$query->select($this->db->quoteName('id'))
					->from($this->db->quoteName('#__fields'))
					->where($this->db->quoteName('name') . '  = ' . $this->db->quote($fieldName));
				$this->db->setQuery($query);
				$fieldId = $this->db->loadResult();

				$query->clear()
					->select(
						$this->db->quoteName(
							array(
								'field_id',
								'category_id'
							)
						)
					)
					->from($this->db->quoteName('#__fields_categories'))
					->where($this->db->quoteName('field_id') . ' = ' . (int) $fieldId)
					->where($this->db->quoteName('category_id') . ' = ' . (int) $this->getState('catid', false));
				$this->db->setQuery($query);
				$result = $this->db->loadResult();

				if (!$result)
				{
					$this->log->add('Custom field ' . $fieldName . ' is not assigned to given category');
				}

				$query->clear()
					->delete($this->db->quoteName('#__fields_values'))
					->where($this->db->quoteName('field_id') . ' = ' . (int) $fieldId)
					->where($this->db->quoteName('item_id') . ' = ' . (int) $id);
				$this->db->setQuery($query)->execute();
				$this->log->add('Removed existing custom field values');

				$importValues = explode('|', $this->getState($fieldName, ''));

				$query->clear()
					->insert($this->db->quoteName('#__fields_values'))
					->columns($this->db->quoteName(array('field_id', 'item_id', 'value')));

				foreach ($importValues as $fieldValues)
				{
					$query->values(
						(int) $fieldId . ',' .
						(int) $id . ',' .
						$this->db->quote($fieldValues)
					);
				}

				$this->db->setQuery($query)->execute();
				$this->log->add('Custom field values added');
			}
		}

		return true;
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function loadTables()
	{
		$this->content = $this->getTable('Content');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function clearTables()
	{
		$this->content->reset();
	}

	/**
	 * Set the attributes field.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function setAttributes()
	{
		// Check for attributes
		if (!$this->getState('attribs', false))
		{
			$attributeFields = array
			(
				'show_title',
				'link_titles',
				'show_intro',
				'show_category',
				'link_category',
				'show_parent_category',
				'link_parent_category',
				'show_author',
				'link_author',
				'show_create_date',
				'show_modify_date',
				'show_publish_date',
				'show_item_navigation',
				'show_icons',
				'show_print_icon',
				'show_email_icon',
				'show_vote',
				'show_hits',
				'show_noauth',
				'urls_position',
				'alternative_readmore',
				'article_layout',
				'show_publishing_options',
				'show_article_options',
				'show_urls_images_backend',
				'show_urls_images_frontend'
			);

			// Get Value from content plugin
			$dispatcher = new \RantaiPluginDispatcher;
			$dispatcher->importPlugins('csviext', $this->db);

			// Fire the plugin to get attributes to import
			$pluginFields = $dispatcher->trigger(
				'getAttributes',
				array(
					'extension' => 'joomla',
					'operation' => 'content',
					'log'       => $this->log
				)
			);

			if (!empty($pluginFields[0]))
			{
				$this->log->add('Attributes added for content swmap plugin', false);
				$attributeFields = array_merge($attributeFields, $pluginFields[0]);
			}

			// Load the current attributes
			$attributes = json_decode($this->content->attribs);

			if (!is_object($attributes))
			{
				$attributes = new \stdClass;
			}

			foreach ($attributeFields as $field)
			{
				if (!$this->getState($field, false))
				{
					if ($this->$field == '*')
					{
						$attributes->$field = '';
					}
					else
					{
						$attributes->$field = $this->getState($field, '');
					}
				}
				else
				{
					$attributes->$field = $this->getState($field, '');
				}
			}

			// Store the new attributes
			$this->setState('attribs', json_encode($attributes));
		}
	}

	/**
	 * Set the images.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function setImages()
	{
		if (!$this->getState('images'))
		{
			$imageFields = array
			(
				'image_intro',
				'float_intro',
				'image_intro_alt',
				'image_intro_caption',
				'image_fulltext',
				'float_fulltext',
				'image_fulltext_alt',
				'image_fulltext_caption'
			);

			// Load the current images
			$images = json_decode($this->content->images);

			if (!is_object($images))
			{
				$images = new \stdClass;
			}

			foreach ($imageFields as $field)
			{
				$images->$field = $this->getState($field, '');
			}

			// Store the new attributes
			$this->setState('images', json_encode($images));
		}
	}

	/**
	 * Set the urls.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function setUrls()
	{
		if (!$this->getState('urls'))
		{
			$urlFields = array
			(
				'urla',
				'urlatext',
				'targeta',
				'urlb',
				'urlbtext',
				'targetb',
				'urlc',
				'urlctext',
				'targetc',
			);

			// Load the current images
			$urls = json_decode($this->content->urls);

			if (!is_object($urls))
			{
				$urls = new \stdClass;
			}

			foreach ($urlFields as $field)
			{
				$urls->$field = $this->getState($field, '');
			}

			// Store the new attributes
			$this->setState('urls', json_encode($urls));
		}
	}

	/**
	 * Set the meta data.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function setMetadata()
	{
		if (!$this->getState('metadata', false))
		{
			$metadataFields = array
			(
				'meta_robots',
				'meta_author',
				'meta_rights',
				'meta_xreference'
			);

			// Load the current attributes
			$metadata = json_decode($this->content->metadata);

			if (!is_object($metadata))
			{
				$metadata = new \stdClass;
			}

			foreach ($metadataFields as $field)
			{
				$newField = str_ireplace('meta_', '', $field);

				if ($this->getState($field, false))
				{
					if ($this->getState($field, '') == '*')
					{
						$metadata->$field = '';
					}
					else
					{
						$metadata->$newField = $this->getState($field, '');
					}
				}
				elseif (!isset($metadata->$newField))
				{
					$metadata->$newField = '';
				}
			}

			// Store the new attributes
			$this->setState('metadata', json_encode($metadata));
		}
	}
}
