<?php
/**
 * Users helper file
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: com_virtuemart.php 2052 2012-08-02 05:44:47Z RolandD $
 */

defined('_JEXEC') or die;

/**
 * Joomla User helper class.
 *
 * @package     CSVI
 * @subpackage  JUsers
 * @since       6.0
 */
class Com_UsersHelperCom_Users
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log = null;

	/**
	 * Fields helper
	 *
	 * @var    CsviHelperImportFields
	 * @since  6.0
	 */
	protected $fields = null;

	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db = null;

	/**
	 * Constructor.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate.
	 * @param   CsviHelperLog       $log       An instance of CsviHelperLog.
	 * @param   CsviHelperFields    $fields    An instance of CsviHelperFields.
	 * @param   JDatabaseDriver     $db        Database connector.
	 *
	 * @since   4.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperFields $fields, JDatabaseDriver $db)
	{
		$this->template = $template;
		$this->log = $log;
		$this->fields = $fields;
		$this->db = $db;
	}

	/**
	 * Get the user id, this is necessary for updating existing users.
	 *
	 * @return  mixed  ID of the user if found | False otherwise.
	 *
	 * @since   5.9.5
	 */
	public function getUserId()
	{
		$id = $this->fields->get('id');

		if ($id)
		{
			return $id;
		}
		else
		{
			$email = $this->fields->get('email');

			if ($email)
			{
				$query = $this->db->getQuery(true)
					->select('id')
					->from($this->db->quoteName('#__users'))
					->where($this->db->quoteName('email') . '  = ' . $this->db->quote($email));
				$this->db->setQuery($query);
				$this->log->add('Find the user ID');

				return $this->db->loadResult();
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Get the user group id, this is necessary for updating existing user groups.
	 *
	 * @return  mixed  ID of the user if found | False otherwise.
	 *
	 * @since   6.5.0
	 */
	public function getUsergroupId()
	{
		$id = $this->fields->get('id');

		if ($id)
		{
			return $id;
		}
		else
		{
			$title = $this->fields->get('title');

			if ($title)
			{
				$query = $this->db->getQuery(true)
						->select('id')
						->from($this->db->quoteName('#__usergroups'))
						->where($this->db->quoteName('title') . '  = ' . $this->db->quote($title));
				$this->db->setQuery($query);
				$this->log->add('Find the user group ID');

				return $this->db->loadResult();
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Get the user accesslevel id, this is necessary for updating existing access level.
	 *
	 * @param   string  $title  The name of the access level.
	 *
	 * @return  mixed  ID of the user if found | False otherwise.
	 *
	 * @since   7.1.0
	 */
	public function getAccessLevelId($title)
	{
		if (!$title)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__viewlevels'))
			->where($this->db->quoteName('title') . '  = ' . $this->db->quote($title));
		$this->db->setQuery($query);
		$this->log->add('Find the access level ID');
		$id = $this->db->loadResult();

		if (!$id)
		{
			$this->log->add('Access level ID not found for title ' . $title);
		}

		return $id;
	}

	/**
	 * Load the needed field value of the user group.
	 *
	 * @param   string  $name          The name of the user group.
	 * @param   string  $fieldSelect   The field to select.
	 * @param   string  $fieldToCheck  The field to check with.
	 *
	 * @return  mixed  The ID or Name of the user group.
	 *
	 * @since   7.1.0
	 */
	public function getAccessLevelGroupId($name, $fieldSelect, $fieldToCheck)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($fieldSelect))
			->from($this->db->quoteName('#__usergroups'))
			->where($this->db->quoteName($fieldToCheck) . ' = ' . $this->db->quote($name));
		$this->db->setQuery($query);

		$result = $this->db->loadResult();

		return $result;
	}
}
