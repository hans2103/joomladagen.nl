<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Daniel Dimitrov (http://compojoom.com)
 **/

defined('_JEXEC') or die('RestrictedAccess');

require_once(JPATH_COMPONENT_ADMINISTRATOR . '/libraries/joomlamailer/interfaces/hotActivity.php');

/**
 * 	@author Daniel Dimitrov (http://compojoom.com)
 */
class joomailerAmbrasubsActivity  implements hotActivity {

	private static $instance = null;
    private static $app = null;

	/**
	 * Implements the singleton pattern
	 * @return object
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new joomailerAmbrasubsActivity();
		}

        if (self::$app == null) {
            self::$app = JFactory::getApplication();
        }
        
		return self::$instance;
	}


	/**
	 * @return object $results
	 */
	public function getActivity() {
		$userId = self::$app->input->getUint('uid');

    	$db = JFactory::getDBO();
    	$query = 'SELECT u.created_datetime as crdate, t.title, FORMAT(t.value, 2) as price, "ambrasubs" AS joomailerProductCategory'
				. ' FROM ' . $db->qn('#__ambrasubs_users2types') . ' AS u '
				. ' LEFT JOIN ' . $db->qn('#__ambrasubs_types') . ' AS t '
				. ' ON u.typeid = t.id'
				. ' WHERE u.userid = '. $db->q($userId)
				. ' AND status = 1'
				. ' ORDER BY crdate DESC';

		$db->setQuery($query, 0, 5);
		$results = $db->loadObjectList('crdate');

		return $results;
	}

	private function getAllSubscriptions() {
		$db = JFactory::getDBO();
		$query = 'SELECT count(id) as count FROM ' . $db->qn('#__ambrasubs_types')
				. ' WHERE ' . $db->qn('published') . '=' . $db->q(1);

		$db->setQuery($query);
		return $db->loadObject()->count;
	}
	private function getSubscriptionsUserHas() {
		$userId = self::$app->input->getUint('uid');
		$db = JFactory::getDBO();
		$query = 'SELECT count(*) as count FROM ' . $db->qn('#__ambrasubs_users2types')
				. ' WHERE userid =' . $db->q($userId)
				. ' AND expires_datetime >= NOW()'
				. ' AND status = 1';

		$db->setQuery($query);

		return $db->loadObject()->count;
	}

	public function countSubscriptionsPerUser() {
		$db = JFactory::getDBO();
		$query = 'SELECT COUNT(ut.u2tid) AS count, u.email AS email '
				. ' FROM ' . $db->qn('#__ambrasubs_users2types') . ' AS ut'
				. ' LEFT JOIN ' . $db->qn('#__users') . ' AS u ON u.id = ut.userid'
				. ' WHERE status = 1'
				. ' GROUP BY ' . $db->qn('u.email');
		$db->setQuery($query);
		$result = $db->loadAssocList('email');

		return $result;
	}

	public function getAllUserHotnessValue() {
		$hotnessValue = 0;

		$allSubscriptions = $this->getAllSubscriptions();
		$allUserSubscriptions = $this->countSubscriptionsPerUser();

		foreach($allUserSubscriptions as $key => $value) {
			if ($value['count'] == 1) {
				$hotnessValue = 1;
			}
			if (($value['count'] > 1) && ($value['count'] != $allSubscriptions)) {
				$hotnessValue = 2;
			}
			if ($value['count'] == $allSubscriptions || $value['count'] > $allSubscriptions) {
				$hotnessValue = 3;
			}
			$allUserSubscriptions[$key]['hotness'] = $hotnessValue;
		}

		return $allUserSubscriptions;
	}

	/**
	 *
	 * @return int
	 */
	public function getHotnessValue() {
		$hotnessValue = 0;

		$allSubscriptions = $this->getAllSubscriptions();
		$userSubscriptions = $this->getSubscriptionsUserHas();

		if ($userSubscriptions == 1) {
			$hotnessValue = 1;
		}
		if (($userSubscriptions > 1) && ($userSubscriptions != $allSubscriptions)) {
			$hotnessValue = 2;
		}
		if ($allSubscriptions != 0 && $userSubscriptions == $allSubscriptions) {
			$hotnessValue = 3;
		}

		return $hotnessValue;
	}
}
?>
