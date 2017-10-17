<?php

/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * vendor Table class
 *
 * @since  1.6
 */
class TjvendorsTablevendor extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'TjvendorsTablevendor', array('typeAlias' => 'com_tjvendors.vendor'));

		parent::__construct('#__tjvendors_vendors', 'vendor_id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable:bind
	 * @since   1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (!JFactory::getUser()->authorise('core.admin', 'com_tjvendors.vendor.' . $array['vendor_id']))
		{
			$actions = JAccess::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_tjvendors/access.xml',
				"/access/section[@name='vendor']/"
			);
			$default_actions = JAccess::getAssetRules('com_tjvendors.vendor.' . $array['vendor_id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action)
			{
				$array_jaccess[$action->name] = $default_actions[$action->name];
			}

			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of JAccessRule objects.
	 *
	 * @return  array
	 */
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess)
		{
			$actions = array();

			foreach ($jaccess->getData() as $group => $allow)
			{
				$actions[$group] = ((bool) $allow);
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		jimport('joomla.filesystem.file');

		$db = JFactory::getDbo();
		$this->alias = trim($this->alias);

		if (!$this->alias)
		{
			$this->alias = $this->vendor_title;
		}

		if ($this->alias)
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$this->alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
			}
			else
			{
				$this->alias = JFilterOutput::stringURLSafe($this->alias);
			}
		}

		// Check if event with same alias is present
		$table = JTable::getInstance('Vendor', 'TjVendorsTable', array('dbo', $db));

		if ($table->load(array('alias' => $this->alias)) && ($table->vendor_id != $this->vendor_id || $this->vendor_id == 0))
		{
			$msg = JText::_('COM_TJVENDORS_SAVE_ALIAS_WARNING');

			while ($table->load(array('alias' => $this->alias)))
			{
				$this->alias = JString::increment($this->alias, 'dash');
			}

			JFactory::getApplication()->enqueueMessage($msg, 'warning');
		}

		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->vendor_id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		$app = JFactory::getApplication();
		$vendor_id = $app->input->get('vendor_id');

		$files = $app->input->files->get('jform', array(), 'raw');
		$array = $app->input->get('jform', array(), 'ARRAY');

		if (! empty($files['vendor_logo']))
		{
			$this->vendor_logo = "";
			$singleFile = $files['vendor_logo'];

			// Check if the server found any error.
			$fileError = $singleFile['error'];
			$message = '';

			if ($fileError > 0 && $fileError != 4)
			{
				switch ($fileError)
				{
					case 1:
						$message = JText::_('COM_TJVENDOR_FILE_SIZE_EXCEEDS');
						break;
					case 2:
						$message = JText::_('COM_TJVENDOR_FILE_SIZE_EXCEEDS_FORM');
						break;
					case 3:
						$message = JText::_('COM_TJVENDOR_FILE_PARTIAL_UPLOAD_ERROR');
						break;
				}

				if ($message != '')
				{
					$app->enqueueMessage($message, 'warning');

					return false;
				}
			}
			elseif ($fileError == 4)
			{
				if (isset($array['vendor_logo']))
				{
					$this->vendor_logo = $array['vendor_logo'];
				}
			}
			else
			{
				// Check for filesize
				$fileSize = $singleFile['size'];

				if ($fileSize > 26214400)
				{
					$app->enqueueMessage(JText::_('COM_TJVENDOR_FILE_BIGGER_UPLOAD_ERROR'), 'warning');
					$this->setError(JText::_('COM_TJVENDOR_FILE_BIGGER_UPLOAD_ERROR'));

					return false;
				}

				$filename = JFile::stripExt($singleFile['name']);
				$extension = JFile::getExt($singleFile['name']);
				$filename = md5(time()) . $filename;
				$filepath = '/media/com_tjvendor/vendor/' . $filename . '.' . $extension;
				$uploadPath = JPATH_ROOT . $filepath;
				$fileTemp = $singleFile['tmp_name'];

				if (!empty($extension))
				{
					if (($extension !== "png") && ($extension !== "jpg") && ($extension !== "jpeg"))
					{
						$app->enqueueMessage(JText::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'), 'warning');
						$this->setError(JText::_('COM_TJVENDORS_WRONG_FILE_UPLOAD'));

						return false;
					}
				}

				if (! JFile::exists($uploadPath))
				{
					if (! JFile::upload($fileTemp, $uploadPath))
					{
						$app->enqueueMessage(JText::_('COM_TJVENDOR_FILE_MOVING_ERROR'), 'warning');
						$this->setError(JText::_('COM_TJVENDOR_FILE_MOVING_ERROR'));

						return false;
					}
				}

				$this->vendor_logo = $filepath;
			}
		}

		return parent::check();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return   boolean  True on success.
	 *
	 * @since    1.0.4
	 *
	 * @throws Exception
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array(
					$this->$k
				);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				throw new Exception(500, JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (! property_exists($this, 'checked_out') || ! property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery('UPDATE `' . $this->_tbl . '`' . ' SET `state` = ' . (int) $state . ' WHERE (' . $where . ')' . $checkin);
		$this->_db->execute();

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin each row.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		return true;
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see JTable::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_tjvendors.vendor.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   JTable   $table  Table name
	 * @param   integer  $id     Id
	 *
	 * @see JTable::_getAssetParentId
	 *
	 * @return mixed The id on success, false on failure.
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = JTable::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_tjvendors');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}
}
