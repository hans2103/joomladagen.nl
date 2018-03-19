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

JFormHelper::loadFieldClass('list');

/**
 * Loads a list of available fields.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.6.0
 */
class CsviFormFieldLogdetailsresults extends JFormFieldList
{
	/**
	 * The type of field
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'logdetailsresults';

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
		$runId = JFactory::getApplication()->input->getInt('run_id', 0);
		$options = array();
		$db = JFactory::getDbo();

		if ($runId)
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('result', 'text'))
				->select($db->quoteName('result', 'value'))
				->from($db->quoteName('#__csvi_logdetails', 'd'))
				->leftJoin(
					$db->quoteName('#__csvi_logs', 'l')
					. ' ON ' .
					$db->quoteName('d.csvi_log_id') . ' = ' . $db->quoteName('l.csvi_log_id')
				)
				->where($db->quoteName('l.csvi_log_id') . ' = ' . (int) $runId)
				->group($db->quoteName('result'));
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
