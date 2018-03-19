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
 * Select list form field with user groups
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Csvij2storeFormFieldCustomergroup extends JFormFieldCsviForm
{
	/**
	 * Type of field
	 *
	 * @var    string
	 * @since  7.3.0
	 */
	protected $type = 'Customergroup';

	/**
	 * Get the options.
	 *
	 * @return  array  An array of user groups.
	 *
	 * @since   7.3.0
	 *
	 * @throws  RuntimeException
	 *
	 */
	protected function getOptions()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id', 'value') . ',' . $this->db->quoteName('title', 'text'))
			->from($this->db->quoteName('#__usergroups'));
		$this->db->setQuery($query);
		$options = $this->db->loadObjectList();

		if (0 === count($options))
		{
			$options = array();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
