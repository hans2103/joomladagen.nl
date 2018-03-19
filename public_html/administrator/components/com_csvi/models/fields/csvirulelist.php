<?php
/**
 * @package     CSVI
 * @subpackage  Fields
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
 * Select list with rules.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.0
 */
class JFormFieldCsviRulelist extends JFormFieldCsviForm
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'CsviRulelist';

	/**
	 * Get the rules.
	 *
	 * @return  array  The list of rules.
	 *
	 * @since   6.0
	 *          
	 * @throws  RuntimeException
	 */
	protected function getOptions()
	{
		// Load the available rules
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('csvi_rule_id', 'value') . ', ' . $this->db->quoteName('name', 'text'))
			->from($this->db->quoteName('#__csvi_rules'))
			->where($this->db->quoteName('action') . ' = ' . $this->db->quote($this->element['action']))
			->order($this->db->quoteName('ordering'));
		$this->db->setQuery($query);

		$rules = $this->db->loadObjectList();

		if (!is_array($rules))
		{
			$rules = array();
		}

		return array_merge(parent::getOptions(), $rules);
	}
}
