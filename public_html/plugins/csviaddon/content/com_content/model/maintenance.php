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

defined('_JEXEC') or die;

/**
 * Content maintenance.
 *
 * @package     CSVI
 * @subpackage  JoomlaContent
 * @since       6.5.0
 */
class Com_ContentMaintenance
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
	 * Load a number of maintenance tasks.
	 *
	 * @return  array  List of available operations.
	 *
	 * @since   7.1.0
	 */
	public function getOperations()
	{
		return array('options' => array(
			''               => JText::_('COM_CSVI_MAKE_CHOICE'),
			'refreshsefurls' => JText::_('COM_CSVI_REFRESHSEFURLS_LABEL'),
		)
		);
	}

	/**
	 * Load the options for a selected operation.
	 *
	 * @param   string  $operation  The operation to get the options for
	 *
	 * @return  string  The options for a selected operation.
	 *
	 * @since   7.1.0
	 */
	public function getOptions($operation)
	{
		switch ($operation)
		{
			case 'refreshsefurls':
				$layoutPath = JPATH_PLUGINS . '/csviaddon/csvi/com_csvi/layouts';
				$layout     = new JLayoutFile('maintenance.' . $operation, $layoutPath);

				return $layout->render();
			default:
				return '<span class="help-block">' . JText::_('COM_CSVI_' . $operation . '_DESC') . '</span>';
				break;
		}
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
							'address',
							'latitude',
							'lognitude',
							'type',
							'icon',
							'theicon',
							'zoom',
							'panoramioitem',
							'adsenseitem',
							'publisher',
							'formatitem',
							'positionitem'
						);

			foreach ($fields as $field)
			{
				$query->values(
					$this->db->quote($field) . ',' .
					$this->db->quote($field) . ',' .
					$this->db->quote('content') . ',' .
					$this->db->quote('com_content') . ',' .
					$this->db->quote('import')
				);
				$query->values(
					$this->db->quote($field) . ',' .
					$this->db->quote($field) . ',' .
					$this->db->quote('content') . ',' .
					$this->db->quote('com_content') . ',' .
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
				->where($this->db->quoteName('context') . ' = ' . $this->db->quote('com_content.article'));
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
						$this->db->quote('content') . ',' .
						$this->db->quote('com_content') . ',' .
						$this->db->quote('import')
					);
					$query->values(
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote('content') . ',' .
						$this->db->quote('com_content') . ',' .
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
		return 78;
	}

	/**
	 * Refresh the SEF URLs.
	 *
	 * @param   JInput  $input  An instance of JInput.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @throws  \Exception
	 *
	 * @since   7.1.0
	 */
	public function refreshSefUrls(JInput $input)
	{
		// Load all the needed helpers
		$settings = new CsviHelperSettings($this->db);
		$template = new CsviHelperTemplate($input->getInt('template', 0));
		$sef      = new CsviHelperSef($settings, $template, $this->log);
		require_once JPATH_SITE . '/components/com_content/helpers/route.php';

		// Make sure the languages are sorted base on locale instead of random sorting
		$languages = JLanguageHelper::createLanguageList('', JPATH_SITE, true, true);

		if (count($languages) > 1)
		{
			usort(
				$languages,
				function ($a, $b)
				{
					return strcmp($a['value'], $b['value']);
				}
			);
		}

		// Clean the SEF URL table
		if ($input->get('emptytable', false))
		{
			$this->db->truncateTable('#__csvi_sefurls');
		}

		// Get all the products
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'catid')))
			->from($this->db->quoteName('#__content'));
		$this->db->setQuery($query);

		$records = $this->db->getIterator();
		$urls    = array();

		// Keep track on the number of records
		$count = 1;

		foreach ($records as $record)
		{
			// Collect URLs
			$urls[] = ContentHelperRoute::getArticleRoute($record->id, $record->catid);

			// Process batches of 500 records
			if ($count > 500)
			{
				if ($urls)
				{
					foreach ($languages as $language)
					{
						$sef->getSefUrls($urls, $language['value']);

						$this->log->setLinenumber($this->log->getLinenumber() + count($urls));
					}

					$this->log->setLinenumber($this->log->getLinenumber() + count($urls));

					// Clean up
					$urls = array();
					$count = 0;
				}
			}

			$count++;
		}

		// Process the final batch of URLs
		if ($urls)
		{
			foreach ($languages as $language)
			{
				$sef->getSefUrls($urls, $language['value']);

				$this->log->setLinenumber($this->log->getLinenumber() + count($urls));
			}

			$this->log->setLinenumber($this->log->getLinenumber() + count($urls));
		}

		return true;
	}
}
