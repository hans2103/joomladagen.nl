<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaCategory
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Category maintenance.
 *
 * @package     CSVI
 * @subpackage  JoomlaCategory
 * @since       6.5.0
 */
class Com_CategoriesMaintenance
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
	 * @param   JDatabase       $db          The database class
	 * @param   CsviHelperLog   $log         The CSVI logger
	 * @param   CsviHelperCsvi  $csvihelper  The CSVI helper
	 *
	 * @since   6.5.0
	 */
	public function __construct($db, $log, $csvihelper)
	{
		$this->db = $db;
		$this->log = $log;
		$this->csvihelper = $csvihelper;
	}

	/**
	 * Update Custom available fields that require extra processing.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	public function customAvailableFields()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('name') . ' = ' . $this->db->quote('plg_content_swmap'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('content'));
		$this->db->setQuery($query);

		$extension_id = $this->db->loadResult();

		// Insert fields only when the plugin is installed
		if ($extension_id)
		{
			// Start the query
			$query->clear()
				->insert($this->db->quoteName('#__csvi_availablefields'))
				->columns($this->db->quoteName(array('csvi_name', 'component_name', 'component_table', 'component', 'action')));

			$fields = array
						(
							'catwidthindividual',
							'catheightindividual',
							'panoramiocat',
							'adsensecat',
							'publisher',
							'formatcat',
							'positioncat',
						);

			foreach ($fields as $field)
			{
				$query->values(
					$this->db->quote($field) . ',' .
					$this->db->quote($field) . ',' .
					$this->db->quote('category') . ',' .
					$this->db->quote('com_categories') . ',' .
					$this->db->quote('import')
				);
				$query->values(
					$this->db->quote($field) . ',' .
					$this->db->quote($field) . ',' .
					$this->db->quote('category') . ',' .
					$this->db->quote('com_categories') . ',' .
					$this->db->quote('export')
				);
			}

			$this->db->setQuery($query)->execute();
		}

		if (JComponentHelper::isEnabled('com_fields'))
		{
			// Update Joomla custom fields
			$query->clear()
				->select($this->db->quoteName('name'))
				->from($this->db->quoteName('#__fields'))
				->where($this->db->quoteName('state') . ' = 1')
				->where($this->db->quoteName('context') . ' = ' . $this->db->quote('com_content.categories'));
			$this->db->setQuery($query);

			$customFields = $this->db->loadRowList();

			if ($customFields)
			{
				$query->clear()
					->insert($this->db->quoteName('#__csvi_availablefields'))
					->columns($this->db->quoteName(array('csvi_name', 'component_name', 'component_table', 'component', 'action')));

				foreach ($customFields as $cfield)
				{
					$query->values(
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote('category') . ',' .
						$this->db->quote('com_categories') . ',' .
						$this->db->quote('import')
					);
					$query->values(
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote('category') . ',' .
						$this->db->quote('com_categories') . ',' .
						$this->db->quote('export')
					);
				}

				$this->db->setQuery($query)->execute();
			}
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
		return 35;
	}
}
