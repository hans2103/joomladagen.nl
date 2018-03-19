<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaModule
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * The Joomla module helper class.
 *
 * @package     CSVI
 * @subpackage  JoomlaModule
 * @since       7.4.0
 */
class Com_ModulesHelperCom_Modules
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  7.4.0
	 */
	protected $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.4.0
	 */
	protected $log = null;

	/**
	 * Fields helper
	 *
	 * @var    CsviHelperFields
	 * @since  7.4.0
	 */
	protected $fields = null;

	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  7.4.0
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
	 * @since   7.4.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperFields $fields, JDatabaseDriver $db)
	{
		$this->template = $template;
		$this->log      = $log;
		$this->fields   = $fields;
		$this->db       = $db;
	}

	/**
	 * Get the menu ID.
	 *
	 * @param   string  $menuAlias  The menu alias.
	 *
	 * @return  int  The ID of the menu.
	 *
	 * @since   7.4.0
	 *
	 * @throws  RuntimeException
	 */
	public function getMenuId($menuAlias)
	{
		if (!$menuAlias)
		{
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__menu'))
			->where($this->db->quoteName('alias') . '  = ' . $this->db->quote($menuAlias));
		$this->db->setQuery($query);
		$menuId = $this->db->loadResult();

		if (!$menuId)
		{
			$this->log->add('No Joomla Menu found with alias ' . $menuAlias);
		}

		return $menuId;
	}

	/**
	 * Get the module ID.
	 *
	 * @param   string  $title   The title of module
	 * @param   string  $module  The type of module
	 *
	 * @return  int  The ID of the module.
	 *
	 * @since   7.4.0
	 *
	 * @throws  RuntimeException
	 */
	public function getModuleId($title, $module)
	{
		if (!$title || !$module)
		{
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__modules'))
			->where($this->db->quoteName('module') . '  = ' . $this->db->quote($module))
			->where($this->db->quoteName('title') . '  = ' . $this->db->quote($title));
		$this->db->setQuery($query);
		$moduleId = $this->db->loadResult();

		if (!$moduleId)
		{
			$this->log->add('No module found with title ' . $title . ' and module type ' . $module);
		}

		return $moduleId;
	}
}
