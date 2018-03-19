<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace j2store\com_j2store\model\import;

defined('_JEXEC') or die;

/**
 * Geozone shipping rules import.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.5.0
 */
class Geozonerule extends \RantaiImportEngine
{
	/**
	 * CSVI fields
	 *
	 * @var    \CsviHelperImportFields
	 * @since  7.5.0
	 */
	protected $fields;

	/**
	 * The addon helper
	 *
	 * @var    \Com_J2StoreHelperCom_J2Store
	 * @since  7.5.0
	 */
	protected $helper;

	/**
	 * Geozone table
	 *
	 * @var    \J2StoreTableGeozone
	 * @since  7.5.0
	 */
	private $geozoneTable;

	/**
	 * Geozone rules table
	 *
	 * @var    \J2StoreTableGeozonerule
	 * @since  7.5.0
	 */
	private $geozonerulesTable;

	/**
	 * Start the menu import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   7.5.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
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
					case 'enabled':
						switch (strtolower($value))
						{
							case 'n':
							case 'no':
							case '0':
								$value = 0;
								break;
							default:
								$value = 1;
								break;
						}

						$this->setState($name, $value);
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$geoName = $this->getState('geozone_name', false);

		$requiredFields     = array('geozone_name', 'country_id~country_name~country_isocode_2~country_isocode_3', 'zone_code~zone_name~zone_id');
		$checkMissingFields = $this->fields->checkRequiredFields($requiredFields);

		if (!$checkMissingFields)
		{
			$this->loaded = true;

			if (!$this->getState('geozone_id', false) && !$this->getState('j2store_geozone_id', false))
			{
				$geoZoneId = $this->helper->getGeoZoneId($geoName);

				if (!$geoZoneId)
				{
					$this->geozoneTable->geozone_name = $geoName;
					$this->geozoneTable->enabled      = $this->getState('enabled', 1);
					$this->geozoneTable->store();
					$geoZoneId = $this->geozoneTable->j2store_geozone_id;
				}
				else
				{
					if ($this->getState('geozone_name_new', false))
					{
						$this->geozoneTable->j2store_geozone_id = $geoZoneId;
						$this->geozoneTable->geozone_name       = $this->getState('geozone_name_new');
						$this->geozoneTable->enabled            = $this->getState('enabled', 1);
						$this->geozoneTable->store();
					}
				}

				$this->setState('geozone_id', $geoZoneId);
			}

			if ($this->geozonerulesTable->load($this->getState('j2store_geozonerule_id', 0)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add(\JText::sprintf('COM_FIELDS_WARNING_OVERWRITING_SET_TO_NO', $geoName), false);
					$this->loaded = false;
				}
			}
		}
		else
		{
			$this->loaded = false;
			$this->log->addStats('skipped', 'COM_CSVI_GEOZONESRULES_REQUIRED_FIELDS_MISSING');
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no path or menu ID can be found.
	 *
	 * @since   7.5.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getProcessRecord()
	{
		if (!$this->loaded)
		{
			return false;
		}

		if (!$this->getState('j2store_geozonerule_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('geozone_name', '')));
		}
		else
		{
			$countryField = 'country_name';

			if (!$this->getState('country_id', false) && $this->getState('country_name', false))
			{
				$countryField = 'country_name';
			}

			if (!$this->getState('country_id', false) && $this->getState('country_isocode_2', false))
			{
				$countryField = 'country_isocode_2';
			}

			if (!$this->getState('country_id', false) && $this->getState('country_isocode_3', false))
			{
				$countryField = 'country_isocode_3';
			}

			$this->setState('country_id', $this->helper->getCountryId($this->getState($countryField, false), $countryField));

			if ($this->getState('country_id', false) && ($this->getState('zone_code', false) || $this->getState('zone_name', false)))
			{
				$this->setState(
					'zone_id', $this->helper->getCountryZoneId(
					$this->getState('country_id', false),
					$this->getState('zone_code', false), $this->getState('zone_name', false)
				)
				);
			}

			if (!$this->getState('zone_id', false))
			{
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_J2STORE_GEORULE_ZONE_NOT_EXITS', $this->getState('zone_name', '')));

				return false;
			}

			if (!$this->getState('country_id', false))
			{
				$countryName = ($this->getState('country_name') ? $this->getState('country_name') : '');
				$countryName = ($this->getState('country_isocode_2') ? $this->getState('country_name') : $countryName);
				$countryName = ($this->getState('country_isocode_3') ? $this->getState('country_name') : $countryName);
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_J2STORE_GEORULE_COUNTRY_NOT_EXITS', $countryName));

				return false;
			}

			if (strtoupper($this->getState('geozone_name_delete')) === 'Y')
			{
				$this->geozoneTable->delete($this->getState('geozone_id', false));
				$query = $this->db->getQuery(true)
					->delete($this->db->quoteName('#__j2store_geozonerules'))
					->where($this->db->quoteName('geozone_id') . '=' . (int) $this->getState('geozone_id', false));
				$this->db->setQuery($query)->execute();
				$this->log->add('Deleted Geo zone rules');
				$this->log->addStats('deleted', 'COM_CSVI_TABLE_J2STORETABLEGEOZONERULE_DELETED');
			}
			else
			{
				$this->geozonerulesTable->bind($this->state);

				if ($this->getState('zone_name_new', false) || $this->getState('zone_code_new', false))
				{
					$this->setState(
						'zone_id', $this->helper->getCountryZoneId(
						$this->getState('country_id', false),
						$this->getState('zone_code_new', false), $this->getState('zone_name_new', false)
					)
					);
				}

				$this->geozonerulesTable->bind($this->state);

				if ($this->geozonerulesTable->check())
				{
					$this->setState('j2store_geozonerule_id', $this->geozonerulesTable->get('j2store_geozonerule_id'));
				}

				try
				{
					$this->geozonerulesTable->store();
					$this->log->add('Geo zone rules saved successfully', false);
				}
				catch (\Exception $e)
				{
					$this->log->add('Cannot add geo zone rules. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', $e->getMessage());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   7.5.0
	 *
	 * @throws  \Exception
	 */
	public function loadTables()
	{
		$this->geozonerulesTable = $this->getTable('Geozonerule');
		$this->geozoneTable      = $this->getTable('Geozone');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   7.5.0
	 */
	public function clearTables()
	{
		$this->geozonerulesTable->reset();
		$this->geozoneTable->reset();
	}
}
