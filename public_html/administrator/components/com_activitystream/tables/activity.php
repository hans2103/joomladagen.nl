<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Hello Table class
 *
 * @since  0.0.1
 */
class ActivityStreamTableActivity extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		$this->setColumnAlias('published', 'state');
		parent::__construct('#__tj_activities', 'id', $db);
	}

	/**
	 * Function to check data
	 *
	 * @return  boolean
	 */
	public function check()
	{
		$jinput = JFactory::getApplication()->input;
		$db = JFactory::getDbo();
		$errors = array();

		// Activity type should not be empty
		if (empty($this->type))
		{
			$errors['type'] = JText::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_TYPE_REQUIRED');
		}

		// Actor and actor_id should not be empty
		if (empty($this->actor_id) || empty($this->actor))
		{
			$errors['actor'] = JText::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_ACTOR_REQUIRED');
		}

		// Object and object_id should not be empty
		if (empty($this->object_id) || empty($this->object))
		{
			$errors['object'] = JText::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_OBJECT_REQUIRED');
		}

		// If there is data in target then target_id should not be empty
		if (!empty($this->target))
		{
			if (empty($this->target_id))
			{
				$errors['target_id'] = JText::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_TARGET_REQUIRED');
			}
		}

		if (count($errors))
		{
			$this->setError(implode($errors, ', '));

			return false;
		}

		return true;
	}
}
