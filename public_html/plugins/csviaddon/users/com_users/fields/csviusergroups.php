<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaUsers
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('CsviForm');

/**
 * Load a list of Joomla user groups.
 *
 * @package     CSVI
 * @subpackage  JoomlaUsers
 * @since       6.2.0
 */
class JFormFieldCsviUserGroups extends JFormFieldCsviForm
{
	protected $type = 'CsviUserGroups';

	/**
	 * Get the user groups.
	 *
	 * @return  array  List of user groups.
	 *
	 * @since   6.2.0
	 */
	protected function getOptions()
	{
		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName('a.id', 'value') . ',' .
				$this->db->quoteName('a.title', 'text') . ',' .
				' COUNT(DISTINCT ' . $this->db->quoteName('b.id') . ') AS ' . $this->db->quoteName('level')
			)
			->from($this->db->quoteName('#__usergroups', 'a'))
			->leftJoin(
				$this->db->quoteName('#__usergroups', 'b')
				. ' ON ' . $this->db->quoteName('a.lft') . ' > ' . $this->db->quoteName('b.lft')
				. ' AND ' . $this->db->quoteName('a.rgt') . ' < ' . $this->db->quoteName('b.rgt')
			)
			->group(
				array(
					$this->db->quoteName('a.id'),
					$this->db->quoteName('a.title'),
					$this->db->quoteName('a.lft'),
					$this->db->quoteName('a.rgt')
				)
			)
			->order($this->db->quoteName('a.lft') . ' ASC');
		$this->db->setQuery($query);
		$options = $this->db->loadObjectList();

		// Check for a database error.
		if (!$options)
		{
			$options = array();
		}

		foreach ($options as &$option)
		{
			$option->text = str_repeat('- ', $option->level) . $option->text;
		}

		return array_merge(parent::getOptions(), $options);
	}
}
