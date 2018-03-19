<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaMenu
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace menus\com_menus\model\export;

defined('_JEXEC') or die;

/**
 * Export Joomla Menus.
 *
 * @package     CSVI
 * @subpackage  JoomlaMenu
 * @since       6.3.0
 */
class Menu extends \CsviModelExports
{
	/**
	 * List of core fields
	 *
	 * @var    array
	 * @since  6.5.0
	 */
	private $corefields = array();

	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   6.3.0
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			// Build something fancy to only get the fieldnames the user wants
			$userfields = array();
			$exportfields = $this->fields->getFields();
			$this->loadCoreFields();

			foreach ($exportfields as $field)
			{
				switch ($field->field_name)
				{
					case 'component':
						$userfields[] = $this->db->quoteName('component_id');
						break;
					case 'template_style':
						$userfields[] = $this->db->quoteName('template_style_id');
						break;
					case 'menu-anchor_title':
					case 'menu-anchor_css':
					case 'menu_image':
					case 'menu_text':
					case 'page_title':
					case 'show_page_heading':
					case 'page_heading':
					case 'pageclass_sfx':
					case 'menu-meta_description':
					case 'menu-meta_keywords':
					case 'robots':
					case 'secure':
						$userfields[] = $this->db->quoteName('params');
						break;
					case 'custom':
						break;
					default:
						if (in_array($field->field_name, $this->corefields))
						{
							$userfields[] = $this->db->quoteName($field->field_name);
						}
						break;
				}
			}

			// Build the query
			$userfields = array_unique($userfields);
			$query = $this->db->getQuery(true);
			$query->select(implode(",\n", $userfields));
			$query->from($this->db->quoteName('#__menu', 'm'));

			// Make sure the ID is always greater than 0 as we don't want to export the root
			$query->where('parent_id > 0');

			// Filter by published state
			$publish_state = $this->template->get('publish_state');

			if ($publish_state != '' && ($publish_state == 1 || $publish_state == 0))
			{
				$query->where($this->db->quoteName('m.published') . ' = ' . (int) $publish_state);
			}

			// Filter by menu type
			$menutype = $this->template->get('menutype', 'mainmenu');

			if ($menutype != '')
			{
				$query->where($this->db->quoteName('m.menutype') . ' = ' . $this->db->quote($menutype));
			}

			// Add a limit if user wants us to
			$limits = $this->getExportLimit();

			// Execute the query
			$this->db->setQuery($query, $limits['offset'], $limits['limit']);
			$records = $this->db->getIterator();
			$this->log->add('Export query' . $query->__toString(), false);

			// Check if there are any records
			$logcount = $this->db->getNumRows();

			if ($logcount > 0)
			{
				foreach ($records as $record)
				{
					$this->log->incrementLinenumber();

					foreach ($exportfields as $field)
					{
						$fieldname = $field->field_name;

						// Set the field value
						if (isset($record->$fieldname))
						{
							$fieldvalue = $record->$fieldname;
						}
						else
						{
							$fieldvalue = '';
						}

						// Process the field
						switch ($fieldname)
						{
							case 'component':
								$query = $this->db->getQuery(true)
									->select($this->db->quoteName('name'))
									->from($this->db->quoteName('#__extensions'))
									->where($this->db->quoteName('extension_id') . ' = ' . (int) $record->component_id);
								$this->db->setQuery($query);

								$fieldvalue = $this->db->loadResult();
								break;
							case 'template_style':
								$query = $this->db->getQuery(true)
									->select($this->db->quoteName('title'))
									->from($this->db->quoteName('#__template_styles'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->template_style_id);
								$this->db->setQuery($query);

								$fieldvalue = $this->db->loadResult();
								break;
							case 'access':
								$query = $this->db->getQuery(true)
									->select($this->db->quoteName('title'))
									->from($this->db->quoteName('#__usergroups'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->access);
								$this->db->setQuery($query);

								$fieldvalue = $this->db->loadResult();
								break;
							default:
								if (!empty($record->params))
								{
									// Check if the field is one of the parameters
									$parameters = json_decode($record->params);

									if (isset($parameters->$fieldname))
									{
										$fieldvalue = $parameters->$fieldname;
									}

									switch ($fieldname)
									{
										case 'show_page_heading':
										case 'published':
										case 'menu_text':
											switch ($fieldvalue)
											{
												case 1:
													$fieldvalue = 'Y';
													break;
												case 0:
													$fieldvalue = 'N';
													break;
											}
											break;
										case 'secure':
											switch ($fieldvalue)
											{
												case 1:
													$fieldvalue = 'Y';
													break;
												case -1:
													$fieldvalue = 'N';
													break;
											}
											break;
									}
								}

								break;
						}

						// Store the field value
						$this->fields->set($field->csvi_templatefield_id, $fieldvalue);
					}

					// Output the data
					$this->addExportFields();

					// Output the contents
					$this->writeOutput();
				}
			}
			else
			{
				$this->addExportContent(\JText::_('COM_CSVI_NO_DATA_FOUND'));

				// Output the contents
				$this->writeOutput();
			}
		}
	}

	/**
	 * Load the core supported fields.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function loadCoreFields()
	{
		// Set the corefields, needed to be able to distinguish component fields
		$this->corefields = array(
			'id',
			'menutype',
			'title',
			'alias',
			'note',
			'path',
			'link',
			'type',
			'published',
			'parent_id',
			'level',
			'component_id',
			'checked_out',
			'checked_out_time',
			'browserNav',
			'access',
			'img',
			'template_style_id',
			'params',
			'lft',
			'rgt',
			'home',
			'language',
			'client_id',
			'template_style',
			'menuordering',
			'menu-anchor_title',
			'menu-anchor_css',
			'menu_image',
			'menu_text',
			'page_title',
			'show_page_heading',
			'pageclass_sfx',
			'menu-meta_description',
			'menu-meta_keywords',
			'robots',
			'secure',
			'page_heading',
		);
	}
}
