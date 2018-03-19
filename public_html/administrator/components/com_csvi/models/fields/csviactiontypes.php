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
 * Loads a list of available fields.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.6.0
 */
class JFormFieldCsviActionTypes extends JFormFieldCsviForm
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'csviactiontypes';

	/**
	 * Get the available fields.
	 *
	 * @return  array  An array of available fields.
	 *
	 * @since   4.3
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	protected function getOptions()
	{
		$options = array();
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('action'))
			->from($this->db->quoteName('#__csvi_logs'))
			->group($this->db->quoteName('action'));

		$this->db->setQuery($query);
		$actions = $this->db->loadColumn();

		if (!empty($actions))
		{
			foreach ($actions as $action)
			{
				$options[] = JHtml::_('select.option', $action, JText::_('COM_CSVI_' . $action));
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
