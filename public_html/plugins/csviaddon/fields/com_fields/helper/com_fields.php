<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaFields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Joomla Fields helper.
 *
 * @package     CSVI
 * @subpackage  JoomlaFields
 * @since       7.2.0
 */
class Com_FieldsHelperCom_Fields
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  7.2.0
	 */
	protected $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.2.0
	 */
	protected $log = null;

	/**
	 * Fields helper
	 *
	 * @var    CsviHelperFields
	 * @since  7.2.0
	 */
	protected $fields = null;

	/**
	 * Database connector
	 *
	 * @var    JDatabase
	 * @since  7.2.0
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
	 * @since   7.2.0
	 */
	public function __construct(
		CsviHelperTemplate $template,
		CsviHelperLog $log,
		CsviHelperFields $fields,
		JDatabaseDriver $db)
	{
		$this->template = $template;
		$this->log = $log;
		$this->fields = $fields;
		$this->db = $db;
	}

	/**
	 * Get the group ID.
	 *
	 * @param   string  $groupName  The group name.
	 *
	 * @return  int  The ID of the group.
	 *
	 * @since   7.2.0
	 *
	 * @throws  RuntimeException
	 */
	public function getGroupId($groupName)
	{
		if (!$groupName)
		{
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select('id')
			->from($this->db->quoteName('#__fields_groups'))
			->where($this->db->quoteName('title') . '  = ' . $this->db->quote($groupName));
		$this->db->setQuery($query);
		$groupId = $this->db->loadResult();

		if (!$groupId)
		{
			$this->log->add("No group found with name " . $groupName);
		}

		return $groupId;
	}

	/**
	 * Get the fields ID based on alias.
	 *
	 * @param   string  $name  The fields alias.
	 *
	 * @return  int  The ID of the field.
	 *
	 * @since   7.2.0
	 *
	 * @throws  RuntimeException
	 */
	public function getFieldsId($name)
	{
		if (!$name)
		{
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select('id')
			->from($this->db->quoteName('#__fields'))
			->where($this->db->quoteName('name') . '  = ' . $this->db->quote($name));
		$this->db->setQuery($query);
		$fieldId = $this->db->loadResult();

		if (!$fieldId)
		{
			$this->log->add("No field found with name " . $name);
		}

		return $fieldId;
	}

	/**
	 * Get the category ID based on it's path.
	 *
	 * @param   string  $category_path  The path of the category
	 *
	 * @return  int  The ID of the category.
	 *
	 * @since   7.2.0
	 */
	public function getCategoryId($category_path)
	{
		$catid = 0;

		if ($category_path)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__categories'))
				->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_content'))
				->where($this->db->quoteName('path') . ' = ' . $this->db->quote($category_path));
			$this->db->setQuery($query);
			$catid = $this->db->loadResult();
			$this->log->add('Found category id for path ' . $category_path);
		}

		return $catid;
	}
}
