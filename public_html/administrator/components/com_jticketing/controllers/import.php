<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . DS . 'controller.php';

jimport('joomla.application.component.controller');
jimport('joomla.html.html');

/**
 * JticketingController helper
 *
 * @package     Jticketing
 * @subpackage  site
 * @since       2.2
 */
class JticketingControllerimport extends JControllerLegacy
{
	/**
	 * setRefund.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function importLocation()
	{
		jimport('joomla.filesystem.file');
		$fileLocation = JPATH_SITE . '/emp_location_mapping.csv';
		$file      = fopen($fileLocation, "r");
		$rowNum    = 0;
		$userData = array();
		$db = JFactory::getDbo();

		while (($data = fgetcsv($file)) !== false)
		{
			if ($rowNum == 0)
			{
				$rowNum++;
				continue;
			}

			// Create a new query object.
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('email') . ' = "' . $data['10'] . '"');
			$db->setQuery($query);

			// Load the results as a list of stdClass objects (see later for more options on retrieving data).
			$userId = $db->loadResult();

			if (!empty($userId))
			{
				$query = $db->getQuery(true);

				// Order it by the ordering field.
				$query->select($db->quoteName(array('id')));
				$query->from($db->quoteName('#__tjlms_user_xref'));
				$query->where($db->quoteName('user_id') . ' = ' . $userId);

				// Reset the query using our newly populated query object.
				$db->setQuery($query);

				// Load the results as a list of stdClass objects (see later for more options on retrieving data).
				$xref_res = $db->loadResult();

				if (!empty($xref_res))
				{
					$query = $db->getQuery(true);

					// Fields to update.
					$fields = array($db->quoteName('training_location') . ' = "' . $data['19'] . '"',);

					// Conditions for which records should be updated.
					$conditions = array($db->quoteName('id') . ' = ' . $xref_res);

					$query->update($db->quoteName('#__tjlms_user_xref'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
				}
				else
				{
					// For join date
					$d = date('Y-m-d', strtotime($data['11']));

					// Create and populate an object.
					$profile = new stdClass;
					$profile->user_id = $userId;
					$profile->join_date = $d;
					$profile->training_location = $data['19'];

					// Insert the object into the user profile table.
					$result = JFactory::getDbo()->insertObject('#__tjlms_user_xref', $profile);
				}
			}
		}

		fclose($file);
		echo "Import Done!";
	}
}
