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
 */
class JFormFieldCountries extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'countries';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
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

		// Select the required fields from the table.
		$query->select('c.id, c.country, c.country_jtext');
		$query->from('`#__tj_country` AS c');

		if ($client)
		{
			$query->where('c.' . $client .' = 1');
		}

		$query->order($db->escape('c.ordering ASC'));

		$db->setQuery($query);

		// Get all countries.
		$countries = $db->loadObjectList();

		$options = array();

		// Load lang file for countries
		$lang = JFactory::getLanguage();
		$lang->load('tjgeo.countries', JPATH_SITE, null, false, true);

		foreach ($countries as $c)
		{
			if ($lang->hasKey(strtoupper($c->country_jtext)))
			{
				$c->country = JText::_($c->country_jtext);
			}

			$options[] = JHtml::_('select.option', $c->id, $c->country);
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
