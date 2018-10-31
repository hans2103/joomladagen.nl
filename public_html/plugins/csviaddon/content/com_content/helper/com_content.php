<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaContent
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * The Joomla content helper class.
 *
 * @package     CSVI
 * @subpackage  JoomlaContent
 * @since       6.0
 */
class Com_ContentHelperCom_Content
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
	 * @param   JDatabaseDriver     $db        Database connector.
	 *
	 * @since   4.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperFields $fields, JDatabaseDriver $db)
	{
		$this->template = $template;
		$this->log      = $log;
		$this->fields   = $fields;
		$this->db       = $db;
	}

	/**
	 * Get the content id, this is necessary for updating existing content.
	 *
	 * @param   string  $alias       The article alias
	 * @param   string  $categoryId  The id of the category
	 *
	 * @return  mixed  Int The ID of the article | False if ID has not been found.
	 *
	 * @since   5.3
	 */
	public function getContentId($alias, $categoryId)
	{
		if ($alias && $categoryId)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__content'))
				->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias))
				->where($this->db->quoteName('catid') . ' = ' . (int) $categoryId);
			$this->db->setQuery($query);
			$this->log->add('Find the Joomla content ID');

			return $this->db->loadResult();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the category ID based on it's path.
	 *
	 * @param   string  $category_path  The path of the category
	 *
	 * @return  int  The ID of the category.
	 *
	 * @since   5.3
	 */
	public function getCategoryId($category_path)
	{
		// Set the default category ID
		$categoryId = 2;

		if ($category_path)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__categories'))
				->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_content'))
				->where($this->db->quoteName('path') . ' = ' . $this->db->quote($category_path));
			$this->db->setQuery($query);
			$categoryId = $this->db->loadResult();

			$this->log->add('Find the category ID and I found category ID ' . $categoryId);

			if (empty($categoryId))
			{
				$categoryId = 2;
			}
		}

		$this->fields->set('catid', $categoryId);

		return $categoryId;
	}

	/**
	 * Check if a content plugin exists.
	 *
	 * @param   string  $plugin  The name of the plugin to check.
	 *
	 * @return  bool  True if exists | False on failure.
	 *
	 * @since   6.0
	 */
	public function pluginExists($plugin)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('name') . ' = ' . $this->db->quote($plugin))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('content'));
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Get the category ID based on it's path.
	 *
	 * @param   integer  $categoryId  The id of the parent category
	 *
	 * @return  integer  The ID of the category.
	 *
	 * @since   7.6.0
	 */
	public function getSubCategoryIds($categoryId)
	{
		$subCats = $this->getChildren($categoryId);

		if ($subCats)
		{
			foreach ((array) $subCats as $subCat)
			{
				$newCats = $this->getSubCategoryIds($subCat);

				$subCats = array_merge((array) $subCats, $newCats);
			}
		}

		return $subCats;
	}

	/**
	 * Get the category IDs of the children.
	 *
	 * @param   integer  $catId  The id of the parent category
	 *
	 * @return  integer  The IDs of the category.
	 *
	 * @since   7.6.0
	 */
	private function getChildren($catId)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__categories'))
			->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_content'))
			->where($this->db->quoteName('parent_id') . ' = ' . (int) $catId);
		$this->db->setQuery($query);
		$categoryId = $this->db->loadColumn();

		return $categoryId;
	}
}
