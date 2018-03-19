<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaContacts
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Joomla Contacts helper.
 *
 * @package     CSVI
 * @subpackage  JoomlaContacts
 * @since       7.2.0
 */
class Com_ContactHelperCom_Contact
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
	 * Parent ids array
	 *
	 * @var    array
	 * @since  7.2.0
	 */
	protected $parentId = array();

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
		$this->log      = $log;
		$this->fields   = $fields;
		$this->db       = $db;
	}

	/**
	 * Get the user id, this is necessary for updating existing users.
	 *
	 * @param   string  $email  Email of the user
	 *
	 * @return  mixed  ID of the user if found | False otherwise.
	 *
	 * @since   7.2.0
	 */
	public function getUserId($email)
	{
		if (!$email)
		{
			return '';
		}

		$query = $this->db->getQuery(true)
			->select('id')
			->from($this->db->quoteName('#__users'))
			->where($this->db->quoteName('email') . '  = ' . $this->db->quote($email));
		$this->db->setQuery($query);
		$this->log->add('Found the user ID with email ' . $email);
		$userId = $this->db->loadResult();

		if (!$userId)
		{
			$this->log->add('No user found with email ' . $email);
		}

		return $userId;
	}

	/**
	 * Get the contact details based on alias.
	 *
	 * @param   string  $alias  The contact alias.
	 * @param   string  $field  The field to select
	 *
	 * @return  int  The ID of the field.
	 *
	 * @since   7.2.0
	 *
	 * @throws  RuntimeException
	 */
	public function getContactDetails($alias, $field)
	{
		if (!$alias)
		{
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName($field))
			->from($this->db->quoteName('#__contact_details'))
			->where($this->db->quoteName('alias') . '  = ' . $this->db->quote($alias));
		$this->db->setQuery($query);
		$contactDetails = $this->db->loadResult();

		if (!$contactDetails)
		{
			$this->log->add('No contact found with alias ' . $alias);

			return false;
		}

		return $contactDetails;
	}

	/**
	 * Get the category ID based on it's path.
	 *
	 * @param   string  $category_path  The path of the category
	 * @param   string  $alias          The alias of contact
	 *
	 * @return  int  The ID of the category.
	 *
	 * @since   7.2.0
	 */
	public function getCategoryId($category_path, $alias)
	{
		$catid = 4;

		if ($category_path)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__categories'))
				->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_contact'))
				->where($this->db->quoteName('path') . ' = ' . $this->db->quote($category_path));
			$this->db->setQuery($query);
			$catid = $this->db->loadResult();
			$this->log->add('Found category id for path ' . $category_path);

			if (!$catid)
			{
				$catid = $this->createCategory($category_path, $alias);
				$this->log->add('No category id for path ' . $category_path . ' so creating new category');
			}
		}

		return $catid;
	}

	/**
	 * Create the category based on it's path.
	 *
	 * @param   string  $category_path  The path of the category
	 * @param   string  $alias          The alias of contact
	 *
	 * @return  int  The ID of the category.
	 *
	 * @since   7.2.0
	 */
	private function createCategory($category_path, $alias)
	{
		if (!$category_path)
		{
			return '';
		}

		$categorySeparator = $this->template->get('category_separator', '/');
		$categories        = explode($categorySeparator, $category_path);
		$path              = '';
		$catId             = 0;
		$language          = $this->getContactLanguage($alias);

		foreach ($categories as $key => $category)
		{
			if ($path)
			{
				$path = $path . $categorySeparator . $category;
			}
			else
			{
				$path = $category;
			}

			// Initialize a new category
			$categoryTable = JTable::getInstance('Category');
			$categoryTable->load(array('path' => $path, 'extension' => 'com_contact'));
			$categoryId               = $categoryTable->id;
			$this->parentId[$key + 1] = $categoryId;

			if (!$categoryId)
			{
				$categoryTable->extension   = 'com_contact';
				$categoryTable->path        = $path;
				$categoryTable->title       = $category;
				$categoryTable->description = '';
				$categoryTable->published   = 1;
				$categoryTable->access      = 1;
				$categoryTable->language    = ($language) ? $language : '*';
				$parentId                   = 1;

				if (isset($this->parentId[$key]))
				{
					$parentId = $this->parentId[$key];
				}

				$categoryTable->setLocation($parentId, 'last-child');
				$categoryTable->parent_id = $parentId;
				$categoryTable->level     = $key++;

				try
				{
					$categoryTable->check();
					$categoryTable->store();
					$catId                = $categoryTable->id;
					$this->parentId[$key] = $catId;
				}
				catch (\Exception $e)
				{
					$this->log->add('Cannot add category. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_CONTACT_CATEGORY_ERROR', $e->getMessage()));

					return false;
				}
			}
		}

		return $catId;
	}

	/**
	 * Get the contact id based on alias.
	 *
	 * @param   string  $alias  The contact alias.
	 *
	 * @return  int  The ID of the field.
	 *
	 * @since   7.2.0
	 *
	 * @throws  RuntimeException
	 */
	public function getContactId($alias)
	{
		return $this->getContactDetails($alias, 'id');
	}

	/**
	 * Get the contact laguage based on alias.
	 *
	 * @param   string  $alias  The contact alias.
	 *
	 * @return  string  The language of the contact.
	 *
	 * @since   7.2.0
	 *
	 * @throws  RuntimeException
	 */
	public function getContactLanguage($alias)
	{
		return $this->getContactDetails($alias, 'language');
	}
}
