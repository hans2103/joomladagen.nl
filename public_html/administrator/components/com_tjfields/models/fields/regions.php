<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.0
 */
class JFormFieldRegions extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 *
	 * @since	1.6
	 */
	protected $type = 'regions';

	/**
	 * Field to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$client = JFactory::getApplication()->input->get('client', '', 'STRING');
		$options = array();

		// Select the required fields from the table.
		$query->select('r.id, r.region, r.region_jtext');
		$query->from('`#__tj_region` AS r');

		if ($client)
		{
			$query->where('r.' . $client . ' = 1');
		}

		// @TODO - dirty thing starts
		// @TODO - @manoj this might need change before World War - III. ;) LOL
		require_once JPATH_ADMINISTRATOR . '/components/com_tjfields/models/cities.php';
		$TjfieldsModelCities = new TjfieldsModelCities;
		$country = $TjfieldsModelCities->getState('filter.country');

		// @TODO - dirty thing ends

		if ($country)
		{
			$query->where('r.country_id = ' . $country);
			$query->order($db->escape('r.ordering, r.region ASC'));

			$db->setQuery($query);

			// Get all regions.
			$regions = $db->loadObjectList();

			// Load lang file for regions
			$lang = JFactory::getLanguage();
			$lang->load('tjgeo.regions', JPATH_SITE, null, false, true);

			foreach ($regions as $c)
			{
				if ($lang->hasKey(strtoupper($c->region_jtext)))
				{
					$c->region = JText::_($c->region_jtext);
				}

				$options[] = JHtml::_('select.option', $c->id, $c->region);
			}
		}

		if (!$this->loadExternally)
		{
			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
		}

		return $options;
	}

	/**
	 * Method to get a list of options for a list input externally and not from xml.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   2.2
	 */
	public function getOptionsExternally()
	{
		$this->loadExternally = 1;

		return $this->getOptions();
	}
}
