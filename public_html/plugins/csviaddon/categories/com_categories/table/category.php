<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaCategory
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

// Import dependencies
JLoader::register('JTableCategory', JPATH_LIBRARIES . '/legacy/table/category.php');
JLoader::register('JApplicationHelper', JPATH_LIBRARIES . '/cms/application/helper.php');
JLoader::register('JHelper', JPATH_LIBRARIES . '/cms/helper/helper.php');
JLoader::register('JHelperTags', JPATH_LIBRARIES . '/cms/helper/tags.php');
JLoader::register('JHelperContenthistory', JPATH_LIBRARIES . '/cms/helper/contenthistory.php');

/**
 * Joomla category table.
 *
 * @package     CSVI
 * @subpackage  JoomlaCategory
 * @since       6.0
 */
class CategoriesTableCategory extends JTableCategory
{
	/**
	 * Holds the template
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template = null;

	/**
	 * Holds the logger
	 *
	 * @var    CsviHelperLog
	 * @since  7.2.0
	 */
	protected $log = null;

	/**
	 * Inject the template into the table class.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function setTemplate(CsviHelperTemplate $template)
	{
		$this->template = $template;
	}

	/**
	 * Override check method.
	 *
	 * @param   JDate  $date  The Joomla date object.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 */
	public function checkCategory($date)
	{
		// Check for a title.
		if (trim($this->title) == '')
		{
			$this->log->addStats('error', JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY'));

			return false;
		}

		$this->alias = trim($this->alias);

		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$translit = new CsviHelperTranslit($this->template);

		$this->alias = $translit->stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = $date->format('Y-m-d-H-i-s');
		}

		return true;
	}

	/**
	 * Override the store method.
	 *
	 * @param   JDate    $date         The Joomla date object.
	 * @param   int      $userId       The ID of the user running the import.
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 */
	public function storeCategory($date, $userId, $updateNulls = false)
	{
		if ($this->id)
		{
			// Existing category
			$this->modified_time = $date->toSql();
			$this->modified_user_id = $userId;
		}
		else
		{
			// New category
			$this->created_time = $date->toSql();
			$this->created_user_id = $userId;
		}

		// Verify that the alias is unique
		$table = JTable::getInstance('Category', 'JTable', array('dbo' => $this->getDbo()));

		if ($table->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id, 'extension' => $this->extension))
			&& ($table->id != $this->id || $this->id == 0))
		{
			$this->log->addStats('error', JText::_('COM_CSVI_CATEGORY_UNIQUE_ALIAS'));

			return false;
		}

		return $this->storeJTableCategory($date, $userId, $updateNulls);
	}

	/**
	 * Override store method of JTableCategory.
	 *
	 * @param   JDate    $date         The Joomla date object.
	 * @param   int      $userId       The ID of the user running the import.
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 */
	private function storeJTableCategory($date, $userId, $updateNulls = false)
	{
		if ($this->id)
		{
			// Existing category
			$this->modified_time = $date->toSql();
			$this->modified_user_id = $userId;
		}
		else
		{
			// New category
			$this->created_time = $date->toSql();
			$this->created_user_id = $userId;
		}
		// Verify that the alias is unique
		$table = JTable::getInstance('Category', 'JTable', array('dbo' => $this->getDbo()));

		if ($table->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id, 'extension' => $this->extension))
			&& ($table->id != $this->id || $this->id == 0))
		{
			$this->log->addStats('error', JText::_('JLIB_DATABASE_ERROR_CATEGORY_UNIQUE_ALIAS'));

			return false;
		}

		return $this->storeJTableNested($updateNulls);
	}

	/**
	 * Override store method of JTableNested.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 *
	 * @throws  CsviException
	 */
	private function storeJTableNested($updateNulls = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		if ($this->_debug)
		{
			echo "\n" . get_class($this) . "::store\n";
			$this->_logtable(true, false);
		}
		/*
		 * If the primary key is empty, then we assume we are inserting a new node into the
		 * tree.  From this point we would need to determine where in the tree to insert it.
		 */
		if (empty($this->$k))
		{
			/*
			 * We are inserting a node somewhere in the tree with a known reference
			 * node.  We have to make room for the new node and set the left and right
			 * values before we insert the row.
			 */
			if ($this->_location_id >= 0)
			{
				// Lock the table for writing.
				if (!$this->_lock())
				{
					// Error message set in lock method.
					return false;
				}

				// We are inserting a node relative to the last root node.
				if ($this->_location_id == 0)
				{
					// Get the last root node as the reference node.
					$query = $this->getDbo()->getQuery(true);
					$query->select($this->_tbl_key . ', parent_id, level, lft, rgt');
					$query->from($this->_tbl);
					$query->where('parent_id = 0');
					$query->order('lft DESC');

					try
					{
						$this->getDbo()->setQuery($query, 0, 1);
						$reference = $this->getDbo()->loadObject();
						$this->_unlock();
					}
					catch (Exception $e)
					{
						throw new CsviException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $e->getMessage()));
					}

					if ($this->_debug)
					{
						$this->_logtable(false);
					}
				}
				// We have a real node set as a location reference.
				else
				{
					// Get the reference node by primary key.
					if (!$reference = $this->_getNode($this->_location_id))
					{
						// Error message set in getNode method.
						$this->_unlock();

						return false;
					}
				}

				// Get the reposition data for shifting the tree and re-inserting the node.
				if (!($repositionData = $this->_getTreeRepositionData($reference, 2, $this->_location)))
				{
					// Error message set in getNode method.
					$this->_unlock();

					return false;
				}

				// Create space in the tree at the new location for the new node in left ids.
				$query = $this->getDbo()->getQuery(true);
				$query->update($this->_tbl);
				$query->set('lft = lft + 2');
				$query->where($repositionData->left_where);
				$this->_runQuery($query, 'JLIB_DATABASE_ERROR_STORE_FAILED');

				// Create space in the tree at the new location for the new node in right ids.
				$query = $this->getDbo()->getQuery(true);
				$query->update($this->_tbl);
				$query->set('rgt = rgt + 2');
				$query->where($repositionData->right_where);
				$this->_runQuery($query, 'JLIB_DATABASE_ERROR_STORE_FAILED');

				// Set the object values.
				$this->parent_id = $repositionData->new_parent_id;
				$this->level = $repositionData->new_level;
				$this->lft = $repositionData->new_lft;
				$this->rgt = $repositionData->new_rgt;
			}
			else
			{
				// Negative parent ids are invalid
				throw new CsviException(JText::_('JLIB_DATABASE_ERROR_INVALID_PARENT_ID'), 'error');
			}
		}
		/*
		 * If we have a given primary key then we assume we are simply updating this
		 * node in the tree.  We should assess whether or not we are moving the node
		 * or just updating its data fields.
		 */
		else
		{
			// If the location has been set, move the node to its new location.
			if ($this->_location_id > 0)
			{
				if (!$this->moveByReference($this->_location_id, $this->_location, $this->$k))
				{
					// Error message set in move method.
					return false;
				}
			}

			// Lock the table for writing.
			if (!$this->_lock())
			{
				// Error message set in lock method.
				return false;
			}
		}

		// Store the row to the database.
		if (!$this->storeJTable($updateNulls))
		{
			$this->_unlock();

			return false;
		}

		if ($this->_debug)
		{
			$this->_logtable();
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Override store method of JTable.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 *
	 * @throws  CsviException
	 */
	private function storeJTable($updateNulls = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$currentAssetId = 0;

		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->$k)
		{
			$stored = $this->getDbo()->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$stored = $this->getDbo()->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		// If the store failed return false.
		if (!$stored)
		{
			throw new CsviException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', 'error'));
		}

		// If the table is not set to track assets return true.
		if (!$this->_trackAssets)
		{
			return true;
		}

		if ($this->_locked)
		{
			$this->_unlock();
		}

		// Asset Tracking
		$parentId = $this->_getAssetParentId();
		$name = $this->_getAssetName();
		$title = $this->_getAssetTitle();

		$asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$asset->loadByName($name);

		// Re-inject the asset id.
		$this->asset_id = $asset->id;

		// Check for an error.
		if ($error = $asset->getError())
		{
			$this->setError($error);

			return false;
		}

		// Specify how a new or moved node asset is inserted into the tree.
		if (empty($this->asset_id) || $asset->parent_id != $parentId)
		{
			$asset->setLocation($parentId, 'last-child');
		}

		// Prepare the asset to be stored.
		$asset->parent_id = $parentId;
		$asset->name = $name;
		$asset->title = $title;

		if ($this->_rules instanceof JAccessRules)
		{
			$asset->rules = (string) $this->_rules;
		}

		try
		{
			$asset->check();
			$asset->store($updateNulls);
		}
		catch (Exception $e)
		{
			$this->log->add('Asset table. Error: ' . $e->getMessage(), false);
			$this->log->addStats('incorrect', $e->getMessage());

			return false;
		}

		// Create an asset_id or heal one that is corrupted.
		if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
		{
			// Update the asset_id field in this table.
			$this->asset_id = (int) $asset->id;

			$query = $this->getDbo()->getQuery(true);
			$query->update($this->getDbo()->quoteName($this->_tbl));
			$query->set('asset_id = ' . (int) $this->asset_id);
			$query->where($this->getDbo()->quoteName($k) . ' = ' . (int) $this->$k);
			$this->getDbo()->setQuery($query);

			try
			{
				$this->getDbo()->execute();
			}
			catch (Exception $e)
			{
				$this->log->add('Asset table. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', $e->getMessage());

				return false;
			}
		}

		return true;
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
		$this->set('id', null);
	}

	/**
	 * Inject the logger into the table class.
	 *
	 * @param   CsviHelperLog  $log  An instance of CsviHelperLog.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	public function setLogger(CsviHelperLog $log)
	{
		$this->log = $log;
	}

	/**
	 * Check if insert ID has to be kept as given
	 *
	 * @param   int  $categoryId  The category ID.
	 * @param   int  $userId      The logged in user ID.
	 *
	 * @return  int  Insert id of category table.
	 *
	 * @since   7.2.0
	 */
	public function checkId($categoryId, $userId)
	{
		if (!$categoryId)
		{
			return false;
		}

		$query = $this->getDbo()->getQuery(true)
			->select($this->getDbo()->quoteName($this->_tbl_key))
			->from($this->getDbo()->quoteName($this->_tbl))
			->where($this->getDbo()->quoteName($this->_tbl_key) . ' = ' . (int) $categoryId);
		$this->getDbo()->setQuery($query);
		$id = $this->getDbo()->loadResult();
		$insertId = $id;

		if (!$id && $this->template->get('keepcatid', false))
		{
			$query->clear()
				->insert($this->getDbo()->quoteName($this->_tbl))
				->columns(
					$this->getDbo()->quoteName(
						array(
							$this->_tbl_key,
							'parent_id',
							'level',
							'access',
							'language',
							'created_user_id',
							'created_time',
						)
					)
				)
				->values(
					(int) $categoryId . ',1,1,1,' .
					$this->getDbo()->quote('*') .
					',' . $userId .
					',' . $this->getDbo()->quote((new JDate)->toSql())
				);
			$this->getDbo()->setQuery($query)->execute();
			$insertId = $this->getDbo()->insertid();
			$this->log->add('Insert a new Joomla category with id in import file');
		}

		return $insertId;
	}
}
