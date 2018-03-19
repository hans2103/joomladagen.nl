<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('CsviForm');

/**
 * Select list form field with tax profiles.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Csvij2storeFormFieldTaxprofile extends JFormFieldCsviForm
{
	/**
	 * Type of field
	 *
	 * @var    string
	 * @since  7.3.0
	 */
	protected $type = 'Taxprofile';

	/**
	 * Get the options.
	 *
	 * @return  array  An array of tax profiles.
	 *
	 * @since   7.3.0
	 *
	 * @throws  RuntimeException
	 *
	 * @todo    Change to autoloader
	 */
	protected function getOptions()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_taxprofile_id', 'value') . ',' . $this->db->quoteName('taxprofile_name', 'text'))
			->from($this->db->quoteName('#__j2store_taxprofiles'));
		$this->db->setQuery($query);
		$options = $this->db->loadObjectList();

		if (0 === count($options))
		{
			$options = array();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
