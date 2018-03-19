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

use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Content import.
 *
 * @package     CSVI
 * @subpackage  JoomlaContent
 * @since       6.0
 */
class ContentTableContent extends CsviTableDefault
{
	/**
	 * Should rows be tracked as ACL assets?
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_trackAssets = true;

	/**
	 * Table constructor.
	 *
	 * @param   string     $table   Name of the database table to model.
	 * @param   string     $key     Name of the primary key field in the table.
	 * @param   JDatabase  &$db     Database driver
	 * @param   array      $config  The configuration parameters array
	 *
	 * @since   4.0
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__content', 'id', $db, $config);
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 * to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
	 *
	 * @see     JTable::bind
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['articletext']))
		{
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos = preg_match($pattern, $array['articletext']);

			if ($tagPos == 0)
			{
				$this->introtext = $array['articletext'];
				$this->fulltext = '';
			}
			else
			{
				list ($this->introtext, $this->fulltext) = preg_split($pattern, $array['articletext'], 2);
			}
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check
	 * @since   11.1
	 */
	public function check()
	{
		if (trim($this->title) == '')
		{
			$this->log->addStats('error', JText::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		if (trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
		else
		{
			$translit = new \CsviHelperTranslit($this->template);

			$this->alias = $translit->stringURLSafe($this->alias);
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '')
		{
			$this->fulltext = '';
		}

		if (trim($this->introtext) == '' && trim($this->fulltext) == '')
		{
			$this->log->addStats('error', JText::_('JGLOBAL_ARTICLE_MUST_HAVE_TEXT'));

			return false;
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->log->addStats('error', JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

			return false;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey))
		{
			// Only process if not empty

			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", "<", ">");

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace($bad_characters, "", $this->metakey);

			// Create array using commas as delimiter
			$keys = explode(',', $after_clean);
			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(", ", $clean_keys);
		}

		// Check for default settings
		if (empty($this->id))
		{
			if (empty($this->language))
			{
				$this->language = '*';
			}

			if (empty($this->access))
			{
				$this->access = 1;
			}

			if (empty($this->images))
			{
				$this->images = '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}';
			}

			if (empty($this->urls))
			{
				$this->urls = '{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}';
			}

			if (empty($this->attribs))
			{
				$this->attribs = '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}';
			}

			if (empty($this->metadata))
			{
				$this->metadata = '{"robots":"","author":"","rights":"","xreference":""}';
			}
		}

		return true;
	}

	/**
	 * Check if insert id has to be kept as given
	 *
	 * @return  bool  True if row inserted | False otherwise.
	 *
	 * @since   7.2.0
	 */

	public function checkId()
	{
		if (!$this->id)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($this->_tbl_key))
			->from($this->db->quoteName($this->_tbl))
			->where($this->db->quoteName($this->_tbl_key) . ' = ' . (int) $this->id);
		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		if (!$id && $this->template->get('keepid'))
		{
			$query->clear()
				->insert($this->db->quoteName($this->_tbl))
				->columns(array($this->db->quoteName($this->_tbl_key)))
				->values((int) $this->id);
			$this->db->setQuery($query)->execute();
			$this->log->add('Insert a new Joomla content row with id in import file');
		}

		return true;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   6.0
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_content.article.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   6.0
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the parent asset id for the record
	 *
	 * @param   JTable   $table  A JTable object (optional) for the asset parent
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 *
	 * @since   6.0
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;

		// This is a article under a category.
		if ($this->catid)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__categories'))
				->where($this->_db->quoteName('id') . ' = ' . (int) $this->catid);

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Reset the primary key.
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function reset()
	{
		parent::reset();

		// Reset the primary key
		$this->id = null;
	}
}
