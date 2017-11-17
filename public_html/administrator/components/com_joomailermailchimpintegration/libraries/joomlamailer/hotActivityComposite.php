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

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_ADMINISTRATOR . '/libraries/joomlamailer/interfaces/hotActivity.php');

class hotActivityComposite implements hotActivity {

	protected $systems = array();

	/**
	 * @access public
	 * @author Daniel Dimitrov (http://compojoom.com)
	 */
	public function __construct() {
		$this->addSystem();
	}

	/**
	 * This function gets all available systems and initialize their Classes.
	 * @access public
	 * @author Daniel Dimitrov (http://compojoom.com)
	 */
	public function addSystem() {
		$pathToActivitySystems = JPATH_COMPONENT_ADMINISTRATOR . '/libraries/joomlamailer/hotActivitySystems/';

		$files = JFolder::files($pathToActivitySystems);

		foreach ($files as $key => $file) {
			$fileExists = JFile::exists(JPATH_SITE . '/components/com_' . str_replace('.php', '/', $file) . $file);
			if ($fileExists) {
				require_once($pathToActivitySystems . $file);
				$system = 'joomlamailer' . ucfirst(str_replace('.php', '', $file)) . 'Activity';
				$instance = call_user_func(array($system, 'getInstance'));
				$this->systems[] = $instance;
			}
		}
	}

	/**
	 * @access public
	 * @author Daniel Dimitrov (http://compojoom.com)
	 * @return array $activities
	 */
	public function getActivity() {
		$activities = array();

		foreach ($this->systems as $key => $system) {
			$systemActivity = $system->getActivity();
			if ($systemActivity != NULL) {
				$activities = array_merge($activities, $systemActivity);
			}
		}

		rsort($activities);
		$activities = $this->getFirstXElementsFromArray($activities);
		return $activities;
	}

	/**
	 * @param array $array
	 * @param int $howmany
	 * @access private
	 * @author Daniel Dimitrov (http://compojoom.com)
	 * @return array $newArray
	 */
	private function getFirstXElementsFromArray(array $array, $howmany = 10) {
		$i = 1;
		$newArray = array();
		foreach ($array as $key => $value) {
			$newArray[$key] = $value;
			$i++;
			if ($i > $howmany) {
				break;
			}
		}


		return $newArray;
	}

	/**
	 *
	 * @return int $finalHotness
	 */
	public function getHotnessValue() {
		$defaultHotness = 2;
		$value = 0;
		foreach ($this->systems as $key => $system) {
			$value += $system->getHotnessValue();
		}

		$divide = (count($this->systems)) ? count($this->systems) : 1 ;

		$hotnessValue = floor($value / $divide);

		$finalHotness = $hotnessValue + $defaultHotness;

		return $finalHotness;
	}

	public function getAllUserHotnessValue($listId = null) {
		if ($listId == null) {
			return false;
		}
		$users = array();
		$defaultHotness = 2;
		$usersFinalHotness = array();
		$listUsers = $this->getAllListMembers($listId);

		foreach ($this->systems as $key => $system) {
			$usersHotness = $system->getAllUserHotnessValue();

			foreach($listUsers as $key => $value) {
				if (isset($usersHotness[$value['email']])) {
					if (isset($users[$value['email']])) {
						$users[$value['email']] = $users[$value['email']] + $usersHotness[$value['email']]['hotness'];
					} else {
						$users[$value['email']] = $usersHotness[$value['email']]['hotness'];
					}
				}
			}
		}

		$divide = (count($this->systems)) ? count($this->systems) : 1 ;
		foreach ($users as $key => $value) {
			$usersFinalHotness[$key] = floor($value / $divide) + $defaultHotness;
		}
		return $usersFinalHotness;
	}

	public function getAllListMembers($listId) {
		$MC = $this->MC_object();
		$data = array();
		$run = true;
		$page = 0;
		while ($run) {
			$result = $MC->listMembers($listId, '', '', $page, '15000');
			$page++;
			if ($result) {
				$run = true;
				$data = array_merge($data, $result);
			} else {
				$run = false;
			}
		}

		return $data;
	}

	function MC_object() {
		$params = JComponentHelper::getParams('com_joomailermailchimpintegration');
		$MCapi = $params->get('params.MCapi');
		return new joomlamailerMCAPI($MCapi);
	}
}
