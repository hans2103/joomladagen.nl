<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaMenu
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

// Import dependencies
JLoader::register('JTableMenu', JPATH_LIBRARIES . '/legacy/table/menu.php');
JLoader::register('JApplicationHelper', JPATH_LIBRARIES . '/cms/application/helper.php');
JLoader::register('JHelper', JPATH_LIBRARIES . '/cms/helper/helper.php');

/**
 * Joomla menu table.
 *
 * @package     CSVI
 * @subpackage  JoomlaMenu
 * @since       6.5.0
 */
class MenusTableMenu extends JTableMenu
{
	/**
	 * Holds the template
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.5.0
	 */
	protected $template = null;

	/**
	 * Holds the logger
	 *
	 * @var    CsviHelperLog
	 * @since  6.5.0
	 */
	protected $log = null;

	/**
	 * Inject the template into the table class.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	public function setTemplate(CsviHelperTemplate $template)
	{
		$this->template = $template;
	}

	/**
	 * Inject the logger into the table class.
	 *
	 * @param   CsviHelperLog  $log  An instance of CsviHelperLog.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	public function setLogger(CsviHelperLog $log)
	{
		$this->log = $log;
	}

	/**
	 * Override check method.
	 *
	 * @param   JDate  $date  The Joomla date object.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.5.0
	 */
	public function checkMenu($date)
	{
		// Check for a title.
		if (trim($this->get('title')) === '')
		{
			$this->log->addStats('error', JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY'));

			return false;
		}

		// Prepare the alias
		$alias = $this->get('alias');

		if (empty($alias))
		{
			$alias = $this->get('title');
		}

		$this->set('alias', $this->checkAlias($alias, $date));

		return true;
	}

	/**
	 * Verify an alias field.
	 *
	 * @param   string  $alias  The menu alias.
	 * @param   JDate   $date   The Joomla date object.
	 *
	 * @return  string The alias for the given item.
	 *
	 * @since   7.2.0
	 */
	private function checkAlias($alias, $date)
	{
		$alias = trim($alias);

		$translit = new CsviHelperTranslit($this->template);

		$alias = $translit->stringURLSafe($alias);

		if (trim(str_replace('-', '', $alias)) === '')
		{
			$alias = $date->format('Y-m-d-H-i-s');
		}

		return $alias;
	}

	/**
	 * Check if insert ID has to be kept as given
	 *
	 * @param   int     $menuId  The menu ID.
	 * @param   JDate   $date    The Joomla date object.
	 * @param   string  $alias   The menu alias.
	 *
	 * @return  int  Insert id of menu table.
	 *
	 * @since   7.2.0
	 */
	public function checkId($menuId, $date, $alias)
	{
		if (!$menuId)
		{
			return false;
		}

		$query = $this->getDbo()->getQuery(true)
			->select($this->getDbo()->quoteName($this->_tbl_key))
			->from($this->getDbo()->quoteName($this->_tbl))
			->where($this->getDbo()->quoteName($this->_tbl_key) . ' = ' . (int) $menuId);
		$this->getDbo()->setQuery($query);
		$id = $this->getDbo()->loadResult();
		$insertId = $id;

		if (!$id && $this->template->get('keepmenuid', false))
		{
			// Verify the alias
			$alias = $this->checkAlias($alias, $date);

			// Insert a dummy entry for updating later
			$query->clear()
				->insert($this->getDbo()->quoteName($this->_tbl))
				->columns(
					$this->getDbo()->quoteName(
						array(
							$this->_tbl_key,
							'access',
							'level',
							'alias'
						)
					)
				)
				->values((int) $menuId . ',1,1,' . $this->getDbo()->quote($alias));
			$this->getDbo()->setQuery($query)->execute();
			$insertId = $this->getDbo()->insertid();
			$this->log->add('Insert a new Joomla menu with id in import file');
		}

		return $insertId;
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
	 * @since   6.5.0
	 */
	public function storeMenu($date, $userId, $updateNulls = false)
	{
		// Verify that the alias is unique
		$table = JTable::getInstance('Menu', 'JTable', array('dbo' => $this->getDbo()));

		if ($table->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id))
			&& ($table->id != $this->id || $this->id == 0))
		{
			$this->log->addStats('error', JText::_('COM_CSVI_MENU_UNIQUE_ALIAS'));

			return false;
		}

		return $this->storeJTableMenu($date, $userId, $updateNulls);
	}

	/**
	 * Override store method of JTableMenu.
	 *
	 * @param   JDate    $date         The Joomla date object.
	 * @param   int      $userId       The ID of the user running the import.
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.5.0
	 */
	private function storeJTableMenu($date, $userId, $updateNulls = false)
	{
		// Verify that the alias is unique
		$table = JTable::getInstance('Menu', 'JTable', array('dbo' => $this->getDbo()));

		if ($table->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id))
			&& ($table->id != $this->id || $this->id == 0))
		{
			$this->log->addStats('error', JText::_('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS'));

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
	 * @since   6.5.0
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
					}
					catch (Exception $e)
					{
						$this->log->add('Error: ' . $e->getMessage(), false);
						$this->log->addStats('incorrect', $e->getMessage());

						return false;
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
				$this->log->addStats('error', JText::_('JLIB_DATABASE_ERROR_INVALID_PARENT_ID'));

				return false;
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
	 * @since   6.5.0
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

		$this->log->add('Store menu entry');

		// If the store failed return false.
		if (!$stored)
		{
			throw new CsviException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this)));
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
		$name     = $this->_getAssetName();
		$title    = $this->_getAssetTitle();

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
			$this->log->add('Cannot add to assest table. Error: ' . $e->getMessage(), false);
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

			try
			{
				$this->getDbo()->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				$this->log->add('Cannot update asset id. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', $e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Reset the primary key.
	 *
	 * @return  boolean  Always returns true.
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
