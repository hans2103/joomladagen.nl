<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaMenu
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
 * Select list form field with menu types.
 *
 * @package     CSVI
 * @subpackage  JoomlaMenu
 * @since       6.3.0
 */
class JFormFieldCsviMenuMenutype extends JFormFieldCsviForm
{
	/**
	 * Type of field
	 *
	 * @var    string
	 * @since  6.3.0
	 */
	protected $type = 'CsviMenuMenutype';

	/**
	 * Get the options.
	 *
	 * @return  array  An array of customfields.
	 *
	 * @since   6.3.0
	 */
	protected function getOptions()
	{
		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName('m.menutype', 'value')
				. ', IF (' . $this->db->quoteName('t.title') . ' IS NULL
					OR ' . $this->db->quoteName('t.title') . ' = ' . $this->db->quote('') . ', '
						. $this->db->quoteName('m.menutype') . ', '
						. $this->db->quoteName('t.title')
				. ') AS ' . $this->db->quoteName('text')
			)
			->from($this->db->quoteName('#__menu', 'm'))
			->leftJoin(
				$this->db->quoteName('#__menu_types', 't')
				. ' ON ' .
				$this->db->quoteName('t.menutype') . ' = ' . $this->db->quoteName('m.menutype')
			)
			->where($this->db->quoteName('m.id') . ' > 1')
			->group($this->db->quoteName('m.menutype'));
		$this->db->setQuery($query);
		$options = $this->db->loadObjectList();

		if (empty($options))
		{
			$options = array();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
