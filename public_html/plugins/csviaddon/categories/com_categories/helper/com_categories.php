<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaCategories
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Joomla Categories helper.
 *
 * @package     CSVI
 * @subpackage  JoomlaCategories
 * @since       6.0
 */
class Com_CategoriesHelperCom_Categories
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
	 * @var    CsviHelperFields
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
	 * @param   JDatabase           $db        Database connector.
	 *
	 * @since   4.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperFields $fields, JDatabase $db)
	{
		$this->template = $template;
		$this->log      = $log;
		$this->fields   = $fields;
		$this->db       = $db;
	}

	/**
	 * Get the category ID based on it's path.
	 *
	 * @param   string  $category_path  The path of the category
	 * @param   string  $extension      The extension the category belongs to
	 *
	 * @return  int  The ID of the category.
	 *
	 * @since   5.3
	 */
	public function getCategoryId($category_path, $extension)
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__categories'))
			->where($this->db->quoteName('extension') . ' = ' . $this->db->quote($extension))
			->where($this->db->quoteName('path') . ' = ' . $this->db->quote($category_path));
		$this->db->setQuery($query);

		$this->log->add('Find the category ID');

		return $this->db->loadResult();
	}
}
