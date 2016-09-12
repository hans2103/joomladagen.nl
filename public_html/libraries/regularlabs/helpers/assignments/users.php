<?php
/**
 * @package         Regular Labs Library
 * @version         16.9.1281
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once dirname(__DIR__) . '/assignment.php';

class RLAssignmentsUsers extends RLAssignment
{
	public function passUserGroupLevels()
	{
		$user = JFactory::getUser();

		if (!empty($user->groups))
		{
			$groups = array_values($user->groups);
		}
		else
		{
			$groups = $user->getAuthorisedGroups();
		}

		if ($this->params->inc_children)
		{
			$this->setUserGroupChildrenIds();
		}

		return $this->passSimple($groups);
	}

	public function passUsers()
	{
		return $this->passSimple(JFactory::getUser()->get('id'));
	}

	private function setUserGroupChildrenIds()
	{
		$children = $this->getUserGroupChildrenIds($this->selection);

		if ($this->params->inc_children == 2)
		{
			$this->selection = $children;

			return;
		}

		$this->selection = array_merge($this->selection, $children);
	}

	private function getUserGroupChildrenIds($groups)
	{
		$children = array();

		$db = JFactory::getDbo();

		foreach ($groups as $group)
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('parent_id') . ' = ' . (int) $group);
			$db->setQuery($query);

			$group_children = $db->loadColumn();

			if (empty($group_children))
			{
				continue;
			}

			$children = array_merge($children, $group_children);

			$group_grand_children = $this->getUserGroupChildrenIds($group_children);

			if (empty($group_grand_children))
			{
				continue;
			}

			$children = array_merge($children, $group_grand_children);
		}

		$children = array_unique($children);

		return $children;
	}
}
