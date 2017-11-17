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
class joomailerAcctexpActivity  implements hotActivity {

	private static $instance = null;
    private static $app = null;

	/**
	 * Implements the singleton pattern
	 * @return object
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new joomailerAcctexpActivity();
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
		$query = 'SELECT s.signup_date as crdate, p.name as title, p.params as params, "AEC" AS joomailerProductCategory'
				. ' FROM ' . $db->qn('#__acctexp_subscr') . ' AS s '
				. ' LEFT JOIN ' . $db->qn('#__acctexp_plans') . ' AS p '
				. ' ON s.plan = p.id'
				. ' WHERE s.userid = '. $db->q($userId)
				. ' AND status = ' . $db->q('Active')
				. ' ORDER BY crdate DESC';

		$db->setQuery($query, 0, 5);
		$results = $db->loadObjectList('crdate');

		foreach($results as $key => $value) {
			$params = unserialize(base64_decode($value->params));
			$results[$key]->price = money_format('%i', $params['full_amount']);
			unset($results[$key]->params);
		}

		return $results;
	}

	private function getAllSubscriptions() {
		$db = JFactory::getDBO();
		$query = 'SELECT count(id) as count FROM ' . $db->qn('#__acctexp_plans')
				. ' WHERE ' . $db->qn('active') . '=' . $db->q(1);

		$db->setQuery($query);

		return $db->loadObject()->count;
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

	private function countSubscriptionsPerUser() {
		$db = JFactory::getDBO();
		$query = 'SELECT COUNT(s.id) AS count, u.email AS email '
				. ' FROM ' . $db->qn('#__acctexp_subscr') . ' AS s'
				. ' LEFT JOIN ' . $db->qn('#__users') . ' AS u '
				. ' ON u.id = s.userid'
				. ' WHERE status = ' . $db->q('Active')
				. ' GROUP BY ' . $db->qn('u.email');
		$db->setQuery($query);
		$result = $db->loadAssocList('email');

		return $result;
	}

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
		if ($allSubscriptions != 0 && ($userSubscriptions == $allSubscriptions || $userSubscriptions > $allSubscriptions)) {
			$hotnessValue = 3;
		}

		return $hotnessValue;
	}


	private function getSubscriptionsUserHas() {
		$userId = self::$app->input->getUint('uid');
		$db = JFactory::getDBO();
		$query = 'SELECT count(id) as count FROM ' . $db->qn('#__acctexp_subscr')
				. ' WHERE userid =' . $db->q($userId)
				. ' AND status = ' . $db->q('Active');

		$db->setQuery($query);

		return $db->loadObject()->count;
	}
}
