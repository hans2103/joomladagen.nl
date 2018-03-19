<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaMenus
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Joomla! Menus maintenance.
 *
 * @package     CSVI
 * @subpackage  JoomlaMenus
 * @since       6.5.0
 */
class Com_MenusMaintenance
{
	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  6.5.0
	 */
	private $db = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.5.0
	 */
	private $log = null;

	/**
	 * CSVI Helper.
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.5.0
	 */
	private $csvihelper = null;

	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db          The database class
	 * @param   CsviHelperLog    $log         The CSVI logger
	 * @param   CsviHelperCsvi   $csvihelper  The CSVI helper
	 * @param   bool             $isCli       Set if we are running CLI mode
	 *
	 * @since   6.5.0
	 */
	public function __construct(JDatabaseDriver $db, CsviHelperLog $log, CsviHelperCsvi $csvihelper, $isCli = false)
	{
		$this->db         = $db;
		$this->log        = $log;
		$this->csvihelper = $csvihelper;
		$this->isCli      = $isCli;
	}

	/**
	 * Update available fields that require extra processing.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 *
	 * @throws  RuntimeException
	 */
	public function updateAvailableFields()
	{
		$fieldNames = array();
		$components = array();

		// Get the list of XML files, these may contain fieldnames
		$files = JFolder::files(JPATH_ROOT . '/components', '.xml', true, true);

		// Clean out the list further to only have XML files from the tmpl folders
		foreach ($files as $key => $file)
		{
			if (!preg_match("/\\tmpl(.*)\.xml/", $file))
			{
				unset($files[$key]);
			}
		}

		// Loop through the files to see if there are any fields to store
		foreach ($files as $file)
		{
			// Check which extension the XML file belongs to
			$componentFolder = str_replace(JPATH_ROOT . '/components', '', $file);
			$folderParts = explode('/', $componentFolder);

			if (array_key_exists(1, $folderParts))
			{
				// Check if the component is installed
				if (!array_key_exists($folderParts[1], $components))
				{
					$components[$folderParts[1]] = JComponentHelper::isInstalled($folderParts[1]);
				}

				// Only continue if component is installed
				if ($components[$folderParts[1]])
				{
					// Use a streaming approach to support large files
					$form = new XMLReader;

					if ($form->open($file))
					{
						while ($form->read())
						{
							switch ($form->nodeType)
							{
								case (XMLREADER::ELEMENT):
									$nodes[] = $form->name;

									// Check if we are in the metadata sphere
									if ($nodes[0] !== 'metadata')
									{
										break 2;
									}

									if ($form->name === 'field' && $form->hasAttributes)
									{
										// Get the attributes
										while ($form->moveToNextAttribute())
										{
											switch ($form->name)
											{
												case 'name':
													$fieldNames[] = $form->value;
													break;
												case 'type':
													if ($form->value === 'hidden')
													{
														array_pop($fieldNames);
													}
													break;
											}
										}
									}
									break;
							}
						}
					}
				}
			}
		}

		if (count($fieldNames) > 0)
		{
			// Start the query
			$query = $this->db->getQuery(true)
				->insert($this->db->quoteName('#__csvi_availablefields'))
				->columns($this->db->quoteName(array('csvi_name', 'component_name', 'component_table', 'component', 'action')));

			$fieldNames = array_unique($fieldNames);

			foreach ($fieldNames as $csvi_name)
			{
				$query->values(
					$this->db->quote($csvi_name) . ',' .
					$this->db->quote($csvi_name) . ',' .
					$this->db->quote('menu') . ',' .
					$this->db->quote('com_menus') . ',' .
					$this->db->quote('import')
				);
				$query->values(
					$this->db->quote($csvi_name) . ',' .
					$this->db->quote($csvi_name) . ',' .
					$this->db->quote('menu') . ',' .
					$this->db->quote('com_menus') . ',' .
					$this->db->quote('export')
				);
			}

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Threshold available fields for extension
	 *
	 * @return  int Hardcoded available fields
	 *
	 * @since   7.0
	 */
	public function availableFieldsThresholdLimit()
	{
		return 42;
	}
}
