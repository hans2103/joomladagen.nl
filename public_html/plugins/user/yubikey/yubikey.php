<?php
/**
 * @package     YubikeyAuthPlugins
 * @subpackage  User.yubikey
 *
 * @copyright   Copyright (C) 2013 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * YubiKey User plugin
 *
 * @package     YubikeyAuthPlugins
 * @subpackage  User.yubikey
 * @since       1.0
 */
class plgUserYubikey extends JPlugin
{

	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) and $userId > 0)
			{

				// Load the profile data from the database.
				$db = JFactory::getDbo();

				$query = $db->getQuery(true)
					->select(array('profile_key', 'profile_value'))
					->from('#__user_profiles')
					->where('user_id = ' . $db->q($userId))
					->where('profile_key LIKE ' . $db->q('yubikey.%'))
					->order('ordering');
				$db->setQuery($query);

				try
				{
					$results = $db->loadRowList();

					if ($db->getErrorNum())
					{
						$this->_subject->setError($db->getErrorMsg());

						return false;
					}
				}
				catch (Exception $exc)
				{
					$this->_subject->setError($exc->getMessage());

					return false;
				}

				// Merge the profile data.
				$data->yubikey = array();

				foreach ($results as $v)
				{
					$k = str_replace('yubikey.', '', $v[0]);
					$data->yubikey[$k] = $v[1];
				}
			}
		}

		return true;
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 */
	function onContentPrepareForm($form, $data)
	{

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_admin.profile', 'com_users.user', 'com_users.registration', 'com_users.profile')))
		{
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(dirname(__FILE__) . '/form');

		$form->loadFile('yubikey', false);

		return true;
	}

	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['yubikey']) && (count($data['yubikey'])))
		{
			try
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__user_profiles')
					->where('user_id = ' . $db->q($userId))
					->where('profile_key LIKE ' . $db->q('yubikey.%'));
				$db->setQuery($query);

				if (!$db->execute())
				{
					throw new Exception($db->getErrorMsg());
				}

				$tuples = array();
				$order = 1;

				foreach ($data['yubikey'] as $k => $v)
				{
					if ($k == 'publickey')
					{
						if (strlen($v) < 12)
						{
							$v = '';
						}
						elseif (strlen($v) > 32)
						{
							$v = substr($v, 0, -32);
						}
					}

					$tuples[] = '(' . $userId . ', ' . $db->quote('yubikey.' . $k) . ', ' . $db->quote($v) . ', ' . $order++ . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));

				if (!$db->execute())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__user_profiles')
					->where('user_id = ' . $db->q($userId))
					->where('profile_key LIKE ' . $db->q('yubikey.%'));
				$db->setQuery($query);

				if (!$db->execute())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}

}
